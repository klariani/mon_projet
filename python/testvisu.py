import numpy as np
import plotly.graph_objects as go
from fastapi import FastAPI
from pydantic import BaseModel
from fastapi.responses import HTMLResponse
import json

app = FastAPI()

# Modèle de données pour l'API
class TumeurData(BaseModel):
    rayon_moyen: float
    perimetre_moyen: float
    uniformite_moyenne: float
    concavite_moyenne: float
    symetrie_moyenne: float
    dim_fractal_moyenne: float

@app.post("/generate-graph/")
async def generate_graph(data: TumeurData):
    # Extraire les variables importantes
    radius = data.rayon_moyen
    perimeter = data.perimetre_moyen
    smoothness = data.uniformite_moyenne
    concavity = data.concavite_moyenne
    symmetry = data.symetrie_moyenne
    fractal_dim = data.dim_fractal_moyenne

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

    # Obtenir le contenu HTML du graphique
    graph_html = fig.to_html(full_html=False)

    return HTMLResponse(content=graph_html)
