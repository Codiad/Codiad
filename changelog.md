# Branch
  -  The default branch has been renamed!

master is now named under-dev

If you have a local clone, you can update it by running the following commands.


git branch -m master under-dev
git fetch origin
git branch -u origin/under-dev under-dev
git remote set-head origin -a
