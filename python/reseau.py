import pandas as pd
import numpy as np
from sklearn.decomposition import PCA
from sklearn.preprocessing import StandardScaler, LabelEncoder
from sklearn.neural_network import MLPClassifier
import plotly.graph_objects as go
from plotly.subplots import make_subplots
import plotly.offline as pyo
import mysql.connector

# Connexion à la base
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="root",
    database="cancer",
    port=3306
)

# Charger les données depuis la base
query = """
SELECT 
    t.rayon_moyen, t.texture_moyenne, t.perimetre_moyen, t.air_moyenne, 
    t.uniformite_moyenne, t.compact_moyen, t.concavite_moyenne, 
    t.nconcavite_moyenne, t.symetrie_moyenne, t.dim_fractal_moyenne,
    d.libelle_diagnostic AS y
FROM tumeur t
JOIN diagnostic d ON t.code_diagnostic = d.code_diagnostic
"""

df = pd.read_sql(query, conn)
conn.close()

# Prétraitement
X = df.drop(columns="y")
y = df["y"]
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)
pca = PCA(n_components=2)
X_pca = pca.fit_transform(X_scaled)

# Encodage des labels
le = LabelEncoder()
y_encoded = le.fit_transform(y)

# Créer et entraîner le modèle MLP
mlp = MLPClassifier(hidden_layer_sizes=(20, 10), activation='relu', max_iter=1000, random_state=42)
mlp.fit(X_pca, y_encoded)

# Grille de prédiction
x_min, x_max = X_pca[:, 0].min() - 1, X_pca[:, 0].max() + 1
y_min, y_max = X_pca[:, 1].min() - 1, X_pca[:, 1].max() + 1
xx, yy = np.meshgrid(np.linspace(x_min, x_max, 300),
                     np.linspace(y_min, y_max, 300))
grid = np.c_[xx.ravel(), yy.ravel()]
Z = mlp.predict(grid).reshape(xx.shape)

# Créer la figure Plotly
fig = go.Figure()

# Ajouter la frontière de décision
fig.add_trace(
    go.Contour(
        z=Z,
        x=np.linspace(x_min, x_max, 300),
        y=np.linspace(y_min, y_max, 300),
        colorscale="RdBu",
        showscale=False,
        opacity=0.3,
        line_smoothing=0.85,
    )
)

# Ajouter les points de classe (B et M)
for label, color in zip(['B', 'M'], ['blue', 'red']):
    mask = y == label
    fig.add_trace(
        go.Scatter(
            x=X_pca[mask, 0],
            y=X_pca[mask, 1],
            mode="markers",
            name=label,
            marker=dict(color=color, size=6, line=dict(width=1, color='black')),
            legendgroup=label,
            showlegend=True
        )
    )

# Ajouter les contours de densité pour chaque classe
for label, color in zip(['B', 'M'], ['blue', 'red']):
    mask = y == label
    fig.add_trace(
        go.Scatter(
            x=X_pca[mask, 0],
            y=X_pca[mask, 1],
            mode="markers",
            name=f"{label} (densité)",
            marker=dict(color=color, size=4, opacity=0.6),
            legendgroup=f"{label} (densité)",
            showlegend=False  # Pas besoin de légende pour les contours
        )
    )

# Mettre à jour les axes et le layout
fig.update_layout(
    title="Frontière de décision - Réseau de Neurones (MLP)",
    xaxis_title="PC1",
    yaxis_title="PC2",
    width=800,
    height=600
)

# ✅ Export en fichier HTML
pyo.plot(fig, filename="frontieres_decision_mlp.html")
