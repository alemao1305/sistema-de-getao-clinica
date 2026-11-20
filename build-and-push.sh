#!/usr/bin/env bash

set -e

echo "=========================================================="
echo "   CLÍNICA FABRIS — BUILD E PUSH PARA O DOCKER HUB"
echo "=========================================================="
echo ""

IMAGE_NAME="melloinfra/clinica:latest"
IMAGE_TAG_VERSION="melloinfra/clinica:1.0"

echo "[1/3] Compilando assets do frontend (Vite)..."
npm run build

echo ""
echo "[2/3] Construindo imagem Docker de Produção ($IMAGE_NAME)..."
docker build -t "$IMAGE_NAME" -t "$IMAGE_TAG_VERSION" .

echo ""
echo "[3/3] Enviando imagens para o Docker Hub..."
docker push "$IMAGE_NAME"
docker push "$IMAGE_TAG_VERSION"

echo ""
echo "=========================================================="
echo "   IMAGEM ENVIADA COM SUCESSO!"
echo "=========================================================="
echo "Agora você pode acessar o TrueNAS SCALE e colar o arquivo:"
echo "   truenas-custom-app.yaml"
echo "=========================================================="
