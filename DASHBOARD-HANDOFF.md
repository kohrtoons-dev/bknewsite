# BK Traders Dashboard Contributor Handoff

This document is for the developer and any coding assistant working on the BK
Traders dashboard. Read it before making changes.

## Repository and ownership

- Upstream repository: `https://github.com/kohrtoons-dev/bknewsite`
- Protected upstream branch: `main`
- Working branch: `feature/dashboard`
- Submit finished work through a pull request into
  `kohrtoons-dev/bknewsite:main`.
- Do not push directly to upstream `main`.
- Do not deploy directly to staging or production.

## Initial Git setup

First, fork `kohrtoons-dev/bknewsite` into the contributor's GitHub account.
Then run:

```bash
git clone https://github.com/YOUR-USERNAME/bknewsite.git
cd bknewsite
git remote add upstream https://github.com/kohrtoons-dev/bknewsite.git
git fetch upstream
git checkout main
git merge --ff-only upstream/main
git checkout -b feature/dashboard
```

If the repository is already cloned, verify the remotes before working:

```bash
git remote -v
```

`origin` should be the contributor's fork. `upstream` should be
`https://github.com/kohrtoons-dev/bknewsite.git`.

## Working rules

1. Keep all dashboard work on `feature/dashboard` or a more narrowly named
   branch created from it.
2. Inspect the existing site structure and conventions before changing files.
3. Keep dashboard work isolated from the public marketing site wherever
   practical.
4. Do not alter the live deployment workflow, production redirects, analytics,
   ticker cache jobs, legal pages or affiliate links unless the task explicitly
   requires it.
5. Do not remove or overwrite existing site features to make room for the
   dashboard.
6. Preserve responsive behavior and the established BK Traders visual system.
7. Document any database schema, environment variables, external services,
   setup commands and scheduled jobs required by the dashboard.
8. Run relevant tests and review the complete diff before opening the pull
   request.

## Security requirements

Never commit:

- Passwords, API keys, access tokens or private certificates
- `.env` files containing real credentials
- Database exports containing customer information
- cPanel, SSH or production server credentials
- Session data, logs or cache files
- `node_modules`, dependency caches or generated temporary files

Provide a safe `.env.example` containing placeholder names for required
configuration. Validate authorization on the server for every protected
dashboard action; hiding a link or button in the interface is not access
control.

## Saving and submitting work

Commit focused changes with clear messages:

```bash
git add .
git commit -m "Add dashboard foundation"
git push -u origin feature/dashboard
```

Open a pull request using:

- Base repository: `kohrtoons-dev/bknewsite`
- Base branch: `main`
- Head repository: the contributor's fork
- Compare branch: `feature/dashboard`

The pull request description should include:

- What was added or changed
- Screenshots of important interface states
- Setup and migration steps
- New environment variables, using names only—not secret values
- Tests performed
- Known limitations or unfinished work
- Files outside the dashboard area that were changed and why

Do not merge the pull request. The BK Traders repository owner will review,
test and merge approved work.

## Keeping the fork current

Before starting another group of changes:

```bash
git fetch upstream
git checkout main
git merge --ff-only upstream/main
git push origin main
git checkout feature/dashboard
git merge main
```

Resolve merge conflicts on the contributor branch and include the resolution in
the pull request. Never resolve conflicts by force-pushing over upstream `main`.
