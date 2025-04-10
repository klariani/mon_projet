import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
from sklearn.decomposition import PCA
from sklearn.preprocessing import StandardScaler
import mysql.connector

# Connexion à la base de données MySQL
conn = mysql.connector.connect(
    host="localhost",
    port=3306,  # Changez selon votre configuration
    user="root",
    password="root",
    database="cancer"
)

# Requête SQL avec jointure entre les entités tumeur et diagnostic
query = """
SELECT 
    t.rayon_moyen,
    t.texture_moyenne,
    t.perimetre_moyen,
    t.air_moyenne,
    t.uniformite_moyenne,
    t.compact_moyen,
    t.concavite_moyenne,
    t.nconcavite_moyenne,
    t.symetrie_moyenne,
    t.dim_fractal_moyenne,
    d.libelle_diagnostic
FROM tumeur t
JOIN diagnostic d ON t.code_diagnostic = d.code_diagnostic
"""

# Charger les données dans un DataFrame
df = pd.read_sql(query, conn)

# Fermer la connexion
conn.close()

# Sélectionner les colonnes d'intérêt (caractéristiques)
features = ["rayon_moyen", "texture_moyenne", "perimetre_moyen", "air_moyenne",
            "uniformite_moyenne", "compact_moyen", "concavite_moyenne",
            "nconcavite_moyenne", "symetrie_moyenne", "dim_fractal_moyenne"]

# Extraire les données des caractéristiques
X = df[features]

# Standardiser les données
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

# Effectuer PCA
pca = PCA(n_components=2)
X_pca = pca.fit_transform(X_scaled)

# Ajouter les résultats PCA dans le DataFrame pour visualisation
df_pca = pd.DataFrame(X_pca, columns=['PC1', 'PC2'])
df_pca['diagnostic'] = df['libelle_diagnostic']  # La colonne contenant les labels B/M (diagnostic)

# Tracer le biplot
plt.figure(figsize=(10, 7))
plt.scatter(df_pca['PC1'], df_pca['PC2'], c=df_pca['diagnostic'].map({'B': 'blue', 'M': 'red'}),
            label=df_pca['diagnostic'])
plt.title('PCA Biplot')
plt.xlabel('PC1')
plt.ylabel('PC2')

# Ajouter les vecteurs de direction des caractéristiques
for i, feature in enumerate(features):
    plt.arrow(0, 0, pca.components_[0, i], pca.components_[1, i], color='black', alpha=0.5)
    plt.text(pca.components_[0, i] * 1.2, pca.components_[1, i] * 1.2, feature, color='black', fontsize=12)

# Affichage
plt.show()
