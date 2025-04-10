import pandas as pd
import numpy as np
from sklearn.decomposition import PCA
from sklearn.preprocessing import StandardScaler, LabelEncoder
from sklearn.linear_model import LogisticRegression
from sklearn.svm import SVC
import plotly.graph_objects as go
from plotly.subplots import make_subplots
import mysql.connector
import plotly.offline as pyo

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

# Grille de prédiction
x_min, x_max = X_pca[:, 0].min() - 1, X_pca[:, 0].max() + 1
y_min, y_max = X_pca[:, 1].min() - 1, X_pca[:, 1].max() + 1
xx, yy = np.meshgrid(np.linspace(x_min, x_max, 300),
                     np.linspace(y_min, y_max, 300))
grid = np.c_[xx.ravel(), yy.ravel()]

# Modèles
models = {
    "Régression Logistique": LogisticRegression(),
    "SVM (linéaire)": SVC(kernel="linear", probability=True)
}

# Créer sous-figures
fig = make_subplots(rows=1, cols=2, subplot_titles=list(models.keys()))

# Ajout des tracés
for i, (name, model) in enumerate(models.items(), start=1):
    model.fit(X_pca, y_encoded)
    Z = model.predict(grid).reshape(xx.shape)

    # Contour de décision
    fig.add_trace(
        go.Contour(
            z=Z,
            x=np.linspace(x_min, x_max, 300),
            y=np.linspace(y_min, y_max, 300),
            colorscale="RdBu",
            showscale=False,
            opacity=0.3,
            line_smoothing=0.85,
        ),
        row=1, col=i
    )

    # Points de classe
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
                showlegend=(i == 1)  # Légende que pour le 1er graphe
            ),
            row=1, col=i
        )

    # Ajouter les contours de densité pour chaque classe (B et M)
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
            ),
            row=1, col=i
        )

# Mises à jour layout
fig.update_layout(
    title="Frontières de décision : Logistique vs SVM",
    width=1000,
    height=500
)

# ✅ Export en fichier HTML
pyo.plot(fig, filename="frontieres_decision_densite.html")
