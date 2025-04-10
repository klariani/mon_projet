import plotly.graph_objects as go
import numpy as np
import pandas as pd
import json


file_path = "C:/MAMP/htdocs/GestionP/mon_projet/tmp/test.json"
with open(file_path, 'r') as f:
    data = json.load(f)

# Extraire les variables importantes
radius = float(data['rayon_moyen'])
perimeter = float(data['perimetre_moyen'])
smoothness = float(data['uniformite_moyenne'])
concavity = float(data['concavite_moyenne'])
symmetry = float(data['symetrie_moyenne'])
fractal_dim = float(data['dim_fractal_moyenne'])

# Créer une sphère déformée avec du bruit
theta = np.linspace(0, 2 * np.pi, 100)
phi = np.linspace(0, np.pi, 100)
theta, phi = np.meshgrid(theta, phi)

# Déformer la sphère avec du bruit
r = radius * (1 + concavity * np.sin(2 * phi) * np.cos(2 * theta) + np.random.normal(0, 0.1, theta.shape))

# Coordonnées 3D
x = r * np.sin(phi) * np.cos(theta)
y = r * np.sin(phi) * np.sin(theta)
z = r * np.cos(phi)

# Créer la surface 3D sans couleur
fig = go.Figure(data=[go.Surface(x=x, y=y, z=z, surfacecolor=np.zeros_like(z), showscale=False)])

# Mettre à jour le layout
fig.update_layout(title='Visualisation 3D de la tumeur',
                  scene=dict(
                      xaxis_title='X',
                      yaxis_title='Y',
                      zaxis_title='Z'),
                  width=700,
                  margin=dict(r=20, l=10, b=10, t=40))

# Afficher la visualisation
output_file = "C:/MAMP/htdocs/GestionP/mon_projet/tmp/graph.html"
fig.write_html(output_file)
