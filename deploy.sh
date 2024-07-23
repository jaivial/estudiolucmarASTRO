#!/bin/bash
git rm -r --cached .
cd ../dist
git add .
git status
git commit -m "Deploying"
git remote -v
git push dist-branch main




