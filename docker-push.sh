#!/bin/bash

# Docker Push Script for Lavalite Auth
# This script helps you build and push Docker images

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

VERSION="1.0.0"
REGISTRY=${1:-"ghcr"}  # Default to GHCR, can pass "dockerhub" as argument

echo -e "${GREEN}🐳 Lavalite Auth - Docker Build & Push${NC}"
echo "========================================"

# Check if Dockerfile exists
if [ ! -f "Dockerfile" ]; then
    echo -e "${RED}Error: Dockerfile not found!${NC}"
    exit 1
fi

# Test build first
echo -e "${YELLOW}Step 1: Building test image...${NC}"
docker build -t lavalite-erp-test .

if [ $? -ne 0 ]; then
    echo -e "${RED}Build failed!${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Build successful!${NC}"

# Show image size
echo -e "${YELLOW}Image size:${NC}"
docker images lavalite-erp-test | grep lavalite-erp-test

# Ask if user wants to continue
read -p "Continue with push to registry? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Cancelled."
    docker rmi lavalite-erp-test
    exit 0
fi

if [ "$REGISTRY" == "ghcr" ]; then
    echo -e "${YELLOW}Step 2: Pushing to GitHub Container Registry...${NC}"
    
    # Check if logged in to GHCR
    echo "Checking GHCR authentication..."
    if ! docker login ghcr.io --password-stdin <<< "$CR_PAT" 2>/dev/null; then
        echo -e "${RED}Not logged in to GHCR!${NC}"
        echo "Please run:"
        echo "  export CR_PAT=your_github_token"
        echo "  echo \$CR_PAT | docker login ghcr.io -u YOUR_USERNAME --password-stdin"
        exit 1
    fi
    
    # Tag images
    echo "Tagging images..."
    docker tag lavalite-erp-test ghcr.io/lavalite/erp:$VERSION
    docker tag lavalite-erp-test ghcr.io/lavalite/erp:latest
    
    # Push images
    echo "Pushing version $VERSION..."
    docker push ghcr.io/lavalite/erp:$VERSION
    
    echo "Pushing latest..."
    docker push ghcr.io/lavalite/erp:latest
    
    echo -e "${GREEN}✓ Successfully pushed to GHCR!${NC}"
    echo "Image: ghcr.io/lavalite/erp:$VERSION"
    echo "Image: ghcr.io/lavalite/erp:latest"
    
elif [ "$REGISTRY" == "dockerhub" ]; then
    echo -e "${YELLOW}Step 2: Pushing to Docker Hub...${NC}"
    
    # Check if logged in to Docker Hub
    if ! docker info | grep -q "Username"; then
        echo -e "${RED}Not logged in to Docker Hub!${NC}"
        echo "Please run: docker login"
        exit 1
    fi
    
    # Tag images
    echo "Tagging images..."
    docker tag lavalite-erp-test lavalite/auth:$VERSION
    docker tag lavalite-erp-test lavalite/auth:latest
    
    # Push images
    echo "Pushing version $VERSION..."
    docker push lavalite/auth:$VERSION
    
    echo "Pushing latest..."
    docker push lavalite/auth:latest
    
    echo -e "${GREEN}✓ Successfully pushed to Docker Hub!${NC}"
    echo "Image: lavalite/auth:$VERSION"
    echo "Image: lavalite/auth:latest"
    
else
    echo -e "${RED}Unknown registry: $REGISTRY${NC}"
    echo "Usage: ./docker-push.sh [ghcr|dockerhub]"
    exit 1
fi

# Cleanup test image
echo "Cleaning up test image..."
docker rmi lavalite-erp-test

echo -e "${GREEN}✓ All done!${NC}"
