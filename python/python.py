import pandas as pd
import networkx as nx
import numpy as np
import plotly.graph_objects as go
import mysql.connector

# Connexion à MySQL
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="root",
    database="cancer",
    port=3306  # Mets 3306 si MAMP n'utilise pas 8889
)
query = """
    SELECT rayon_moyen, texture_moyenne, perimetre_moyen, air_moyenne,
           uniformite_moyenne, compact_moyen, concavite_moyenne,
           nconcavite_moyenne, symetrie_moyenne, dim_fractal_moyenne
    FROM tumeur
"""
df = pd.read_sql(query, conn)
conn.close()
df = df.dropna()

# 🔹 Calcul de la corrélation
corr_matrix = df.corr()
columns = corr_matrix.columns.tolist()

# 🔹 Position fixe des nœuds (pour éviter qu'ils bougent à chaque seuil)
np.random.seed(42)
pos_3d = {col: np.random.rand(3) for col in columns}

# 🔹 Nœuds (mêmes tout le temps)
node_x = [pos_3d[node][0] for node in columns]
node_y = [pos_3d[node][1] for node in columns]
node_z = [pos_3d[node][2] for node in columns]
node_text = columns

node_trace = go.Scatter3d(
    x=node_x, y=node_y, z=node_z,
    mode="markers+text",
    marker=dict(size=8, color="deepskyblue"),
    text=node_text,
    textposition="top center",
    hoverinfo="text"
)

# 🔹 Fonction pour générer les coordonnées d’arêtes selon un seuil
def get_edge_coords(threshold):
    edge_x, edge_y, edge_z = [], [], []
    for i in range(len(columns)):
        for j in range(i + 1, len(columns)):
            corr = corr_matrix.iloc[i, j]
            if abs(corr) >= threshold:
                x0, y0, z0 = pos_3d[columns[i]]
                x1, y1, z1 = pos_3d[columns[j]]
                edge_x.extend([x0, x1, None])
                edge_y.extend([y0, y1, None])
                edge_z.extend([z0, z1, None])
    return edge_x, edge_y, edge_z

# 🔹 Arêtes pour seuil initial
initial_edge_x, initial_edge_y, initial_edge_z = get_edge_coords(0.5)

edge_trace = go.Scatter3d(
    x=initial_edge_x, y=initial_edge_y, z=initial_edge_z,
    line=dict(width=2, color="gray"),
    hoverinfo="none",
    mode="lines"
)

# 🔹 Générer les étapes du slider (avec uniquement les coordonnées mises à jour)
steps = []
for val in np.linspace(0.1, 1.0, 10):
    x, y, z = get_edge_coords(val)
    step = dict(
        method="restyle",
        args=[{
            "x": [x, node_x],
            "y": [y, node_y],
            "z": [z, node_z]
        }],
        label=f"{val:.1f}"
    )
    steps.append(step)

# 🔹 Création de la figure
fig = go.Figure(data=[edge_trace, node_trace])

fig.update_layout(
    title="Graphe de corrélation 3D interactif avec seuil",
    showlegend=False,
    margin=dict(l=0, r=0, b=0, t=50),
    sliders=[{
        "steps": steps,
        "active": 4,
        "currentvalue": {"prefix": "Seuil : "}
    }],
    scene=dict(
        xaxis=dict(showbackground=False),
        yaxis=dict(showbackground=False),
        zaxis=dict(showbackground=False)
    ),
    paper_bgcolor="white"
)

fig.write_html("graph_tumeurs.html")


