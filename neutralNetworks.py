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
import json
import numpy as np
import joblib
from tensorflow.keras.models import load_model
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel

# Charger le modèle, scaler et encodeur
model = load_model('mon_modele.h5')
scaler = joblib.load('scaler.pkl')
encoder = joblib.load('encoder.pkl')

# Créer l'app FastAPI
app = FastAPI()

# Définir le schéma des données d'entrée avec Pydantic
class TumeurData(BaseModel):
    rayon_moyen: float
    texture_moyenne: float
    perimetre_moyen: float
    air_moyenne: float
    uniformite_moyenne: float
    compact_moyen: float
    concavite_moyenne: float
    nconcavite_moyenne: float
    symetrie_moyenne: float
    dim_fractal_moyenne: float

# Endpoint pour prédiction
@app.post("/predict/")
async def predict(data: TumeurData):
    # Convertir les données en numpy array
    nouvelle_donnee = np.array([[data.rayon_moyen, data.texture_moyenne, data.perimetre_moyen,
                                 data.air_moyenne, data.uniformite_moyenne, data.compact_moyen,
                                 data.concavite_moyenne, data.nconcavite_moyenne,
                                 data.symetrie_moyenne, data.dim_fractal_moyenne]], dtype=float)

    # Normalisation des données
    nouvelle_donnee_scaled = scaler.transform(nouvelle_donnee)

    # Prédiction avec le modèle
    proba = model.predict(nouvelle_donnee_scaled)[0][0]
    prediction = (proba > 0.5).astype(int)

    # Inverser la prédiction
    diagnostic = encoder.inverse_transform([prediction])[0]
    confiance = proba if prediction == 1 else (1 - proba)

    # Retourner le résultat sous forme de JSON
    return {"diagnostic": diagnostic, "confiance": f"{confiance * 100:.2f}%"}