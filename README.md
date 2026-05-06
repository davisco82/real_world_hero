# Real World Hero

Real World Hero is a gamified personal development platform for children.

The app helps kids build real-world life skills through:
- RPG progression systems
- XP and levels
- daily missions
- achievements
- positive reinforcement
- identity-based growth

This is not a chore app.

Children train skills, complete missions, and level up like in a real RPG game.

---

## Core Skill Domains

- Survival
- Time Control
- Self Management
- Team & Family
- Emotional Intelligence
- Social Skills
- Money & Value
- Health & Energy


# Docker setup (PHP 8.3 + Laravel 12 + Livewire 3 + Filament 3)

Projektové požadavky jsou v [requirements.md](/Users/davidvolf/Developer/real_world_hero/requirements.md).

Lokální stack:

- Docker Compose
- MySQL / PostgreSQL
- Redis
- Queues
- Horizon
- Vite
- Tailwind
- Filament

## Make commands

```bash
make help
make up
make down
make logs
```

Nejpoužívanější:

- `make up` - start kontejnerů na pozadí
- `make down` - vypnutí a odstranění kontejnerů
- `make logs` / `make log` - logy všech služeb
- `make ps` - stav kontejnerů
- `make bash` - shell v `app` kontejneru
- `make artisan c='migrate'` - artisan příkaz
- `make composer c='require package/name'` - composer příkaz
- `make queue-up` - spuštění queue workera
- `make horizon-up` - spuštění Horizon

## 1) Start kontejnerů

```bash
make up
```

## 2) Vytvoření Laravel 12 projektu (pokud ještě neexistuje)

```bash
make init
```

## 3) Nastavení prostředí

```bash
cp .env.docker.example .env
```

Vygeneruj APP key:

```bash
make key
```

## 4) Balíčky

```bash
make composer c='require livewire/livewire:^3.0'
make composer c='require filament/filament:"^3.0" -W'
make composer c='require laravel/horizon:^5.0'
```

Inicializace Filamentu:

```bash
make artisan c='filament:install --panels'
```

Publikace Horizon assets/config:

```bash
make artisan c='horizon:install'
```

## 5) Migrace

```bash
make migrate
```

## 6) Queue + Horizon

```bash
make queue-up
make horizon-up
```

## 7) Frontend (Vite + Tailwind)

V druhém terminálu:

```bash
make node
```

## URL

- App: http://localhost:8111
- Vite: http://localhost:5173
- MySQL host: `127.0.0.1:3307`
- Postgres host: `127.0.0.1:5433`
- Redis host: `127.0.0.1:6380`
