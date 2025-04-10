import pandas as pd
import numpy as np
import tensorflow as tf
from tensorflow import keras
from sklearn.preprocessing import StandardScaler, LabelEncoder
import joblib

# Charger les données
df = pd.read_csv("C:/MAMP/htdocs/GestionP/mon_projet/testPrediction.csv")

# Préparation des données
X = df.drop(columns=["y"])
y = df["y"]

# Encoder la variable cible
encoder = LabelEncoder()
y = encoder.fit_transform(y)

# Normalisation des données
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

# Définition du modèle
model = keras.Sequential([
    keras.layers.Dense(32, activation='relu', input_shape=(X_scaled.shape[1],)),
    keras.layers.Dense(16, activation='relu'),
    keras.layers.Dense(1, activation='sigmoid')  # Classification binaire
])

# Compilation du modèle
model.compile(optimizer='adam', loss='binary_crossentropy', metrics=['accuracy'])

# Entraînement du modèle
print("⏳ Entraînement du modèle...")
model.fit(X_scaled, y, epochs=50, batch_size=16, verbose=1)

# Sauvegarde du modèle, scaler et encoder
model.save("mon_modele.h5")
joblib.dump(scaler, "scaler.pkl")
joblib.dump(encoder, "encoder.pkl")

print("✅ Modèle entraîné et sauvegardé !")
