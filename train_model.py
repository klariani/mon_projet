import tensorflow as tf
from tensorflow.keras.applications import VGG16
from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import Flatten, Dense, Dropout
from tensorflow.keras.preprocessing.image import ImageDataGenerator
import json
import datetime
from sklearn.metrics import confusion_matrix, classification_report
import numpy as np

# 📌 Chemins des dossiers
train_dir = r'C:\wamp64\www\GestionP\dataset_Resized\train'
validation_dir = r'C:\wamp64\www\GestionP\dataset_Resized\validation'
test_dir = r'C:\wamp64\www\GestionP\dataset_Resized\test'

TRAINING_LOG_PATH = 'training_log.json'

# 📌 Data augmentation modérée pour performances rapides
train_datagen = ImageDataGenerator(
    rescale=1./255,
    rotation_range=15,
    horizontal_flip=True,
    zoom_range=0.1
)

validation_datagen = ImageDataGenerator(rescale=1./255)
test_datagen = ImageDataGenerator(rescale=1./255)

train_data = train_datagen.flow_from_directory(
    train_dir,
    target_size=(224, 224),
    batch_size=32,
    class_mode='categorical'
)

validation_data = validation_datagen.flow_from_directory(
    validation_dir,
    target_size=(224, 224),
    batch_size=32,
    class_mode='categorical'
)

test_data = test_datagen.flow_from_directory(
    test_dir,
    target_size=(224, 224),
    batch_size=32,
    class_mode='categorical',
    shuffle=False
)

# 📌 Modèle avec Transfer Learning (VGG16) optimisé
base_model = VGG16(input_shape=(224, 224, 3), include_top=False, weights='imagenet')
base_model.trainable = False  # Figer les couches pré-entraînées

model = Sequential([
    base_model,
    Flatten(),
    Dense(256, activation='relu'),
    Dropout(0.5),
    Dense(len(train_data.class_indices), activation='softmax')
])

model.compile(optimizer=tf.keras.optimizers.Adam(learning_rate=0.0001),
              loss='categorical_crossentropy',
              metrics=['accuracy'])

# 📌 Callback personnalisé pour enregistrer logs
def update_training_log(epochs, accuracy, loss):
    log_data = {
        'epochs_trained': epochs,
        'last_training': datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        'accuracy': accuracy,
        'loss': loss
    }
    with open(TRAINING_LOG_PATH, 'w') as f:
        json.dump(log_data, f, indent=4)

class TrainingLogger(tf.keras.callbacks.Callback):
    def on_epoch_end(self, epoch, logs=None):
        if logs:
            update_training_log(epoch + 1, logs.get('accuracy', 0.0), logs.get('loss', 0.0))

# 📌 Entrainement rapide et efficace du modèle
model.fit(
    train_data,
    validation_data=validation_data,
    epochs=10,
    callbacks=[TrainingLogger()]
)

# 📌 Évaluation finale rapide
print("\n✅ Évaluation finale sur données de test")
test_loss, test_accuracy = model.evaluate(test_data)
print(f"🎯 Accuracy finale sur le test: {test_accuracy*100:.2f}%")

# 📌 Rapport complet des prédictions (matrice de confusion)
predictions = model.predict(test_data)
pred_classes = predictions.argmax(axis=1)

print("\nMatrice de confusion :")
print(confusion_matrix(test_data.classes, pred_classes))

print("\nRapport de classification détaillé :")
print(classification_report(test_data.classes, pred_classes, target_names=test_data.class_indices))

# 📌 Sauvegarde finale du modèle en format récent
model.save('model_cnn_classification.keras')

# 📌 Enregistrer les résultats des tests
with open('test_results.json', 'w') as f:
    json.dump({
        'test_accuracy': test_accuracy,
        'test_samples': test_data.samples,
        'evaluation_date': datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    }, f, indent=4)
