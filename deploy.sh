#!/bin/bash

# Change directory to 'dist'
cd dist

git init

git remote add origin https://github.com/jaivial/dist.git

# Add all changes to the staging area
git add . -f

# Commit changes with a message
git commit -m "deployment build"

# Push changes to the remote 'dist-branch'
git push origin main

# Return to the previous directory
cd ..

git init
