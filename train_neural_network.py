import os
import numpy as np
import tensorflow as tf
from tensorflow import keras
from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import Dense, Flatten, Dropout
from tensorflow.keras.preprocessing.image import ImageDataGenerator
from tensorflow.keras.optimizers import Adam
from tensorflow.keras.regularizers import l2

# 📌 Définition des chemins
base_dir = "C:/wamp64/www/GestionP/Dataset"
train_dir = os.path.join(base_dir, "train")
test_dir = os.path.join(base_dir, "test")

# 📌 Paramètres d'entraînement
img_size = (128, 128)
batch_size = 32
epochs = 15
learning_rate = 0.0001

# 📌 Augmentation des données
datagen = ImageDataGenerator(
    rescale=1.0 / 255.0,
    rotation_range=20,
    width_shift_range=0.2,
    height_shift_range=0.2,
    shear_range=0.2,
    zoom_range=0.2,
    horizontal_flip=True,
    validation_split=0.2
)

# 📌 Chargement des données d'entraînement et de validation
train_generator = datagen.flow_from_directory(
    train_dir,
    target_size=img_size,
    batch_size=batch_size,
    class_mode="binary",
    subset="training"
)

val_generator = datagen.flow_from_directory(
    train_dir,
    target_size=img_size,
    batch_size=batch_size,
    class_mode="binary",
    subset="validation"
)

# 📌 Vérification des données chargées
print("Classes détectées:", train_generator.class_indices)

# 📌 Définition du modèle dense amélioré
model = Sequential([
    Flatten(input_shape=(img_size[0], img_size[1], 3)),  # Transformation en vecteur
    Dense(128, activation="relu", kernel_regularizer=l2(0.001)),
    Dropout(0.5),
    Dense(64, activation="relu", kernel_regularizer=l2(0.001)),
    Dropout(0.3),
    Dense(1, activation="sigmoid")  # Classification binaire (malin vs bénin)
])

# 📌 Compilation du modèle
model.compile(
    optimizer=Adam(learning_rate=learning_rate),
    loss="binary_crossentropy",
    metrics=["accuracy"]
)

# 📌 Entraînement du modèle
history = model.fit(
    train_generator,
    epochs=epochs,
    validation_data=val_generator
)

# 📌 Sauvegarde du modèle
model.save("C:/wamp64/www/GestionP/my_neural_network_model.keras")

print("✅ Modèle entraîné et sauvegardé avec succès !")
