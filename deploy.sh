#!/bin/bash
set -e

# Work in the root repository
echo "Working in the root repository"
cd .

# Change to the 'dist' directory
echo "Changing to 'dist' directory"
cd dist
git add . -f
git status
git commit -m "Deploying to GitHub Pages"
git status
git remote add pepe https://github.com/jaivial/dist.git
git remote -v

