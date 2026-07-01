# Deploy no Render (grátis)

## Pré-requisitos

- Conta no **GitHub** (crie em https://github.com/signup)
- **Cartão de crédito** — o Render exige pra verificação, mas **não cobra nada** no plano grátis

---

## Passo 1 — Enviar o código para o GitHub

1. Abra o **PowerShell** e vá até a pasta:
```powershell
cd C:\Users\guilh\source\repos\enxoval
```

2. Inicia o Git e faz o primeiro commit:
```powershell
git init
git add .
git commit -m "primeiro commit"
```

3. No navegador, acesse **https://github.com/new**
   - Nome do repositório: `enxoval`
   - Público
   - **Não** marcar README nem .gitignore
   - Clique em **"Create repository"**

4. Volta no PowerShell e roda (troque `SEU_USUARIO` pelo seu nome do GitHub):
```powershell
git remote add origin https://github.com/SEU_USUARIO/enxoval.git
git push -u origin master
```

> Se pedir senha, use um **token pessoal** (https://github.com/settings/tokens) ou senha normal.

---

## Passo 2 — Criar conta no Render

1. Acesse **https://render.com**
2. Clique em **"Get Started"** → depois **"Sign up"**
3. Escolha **"Continue with GitHub"**
4. Autorize o Render
5. Na página de plano, escolha **"Free"** e coloque os dados do cartão

---

## Passo 3 — Criar o banco de dados

1. No painel do Render, clique em **"New +"** → **"PostgreSQL"**
2. Preencha:
   - Name: `enxoval-db`
   - Database: `enxoval`
   - User: `enxoval`
   - Plan: **Free**
3. Clique em **"Create Database"**
4. Aguarde criar (uns 2 minutos)
5. Copie o campo **"Internal Database URL"** (é algo como `postgres://user:pass@host:5432/db`)

---

## Passo 4 — Criar o Web Service

1. No painel do Render, clique em **"New +"** → **"Web Service"**
2. Conecte com o GitHub e escolha o repositório `enxoval`
3. Preencha:
   - **Name:** `enxoval`
   - **Region:** escolha **Frankfurt** (Europa) — o mais próximo
   - **Branch:** `master`
   - **Runtime:** **Docker** (vai detectar automaticamente pelo Dockerfile)
   - **Plan:** **Free**
4. Clique em **"Advanced"** (botão no final)
5. Clique em **"Add Environment Variable"**
6. Adicione:
   - **Key:** `DATABASE_URL`
   - **Value:** cole o **Internal Database URL** do passo anterior
7. Clique em **"Create Web Service"**

---

## Passo 5 — Aguardar o deploy

Render vai:
1. Fazer o build usando Docker (uns 3-5 minutos)
2. Iniciar o serviço

Depois que terminar, você verá um link tipo:
```
https://enxoval.onrender.com
```

**Acesse esse link.** Pronto, o site está no ar.

---

## Como funciona

| Característica | Plano Grátis |
|---------------|--------------|
| **Sleep** | Se ninguém acessar por 15min, o site "dorme" |
| **Acordar** | Quando alguém acessa, demora 10-15s pra responder |
| **Banco de dados** | Os dados ficam salvos no PostgreSQL (não perde nada) |
| **Dispositivos** | Qualquer um com o link acessa a mesma lista |

---

## Testar localmente (seu PC)

Depois do deploy, se quiser testar no seu computador:

```powershell
cd C:\Users\guilh\source\repos\enxoval\Enxoval.Web
dotnet run
```

Acesse **http://localhost:5000** — vai usar SQLite local, separado do banco do Render.

---

## Precisa de ajuda?

Se algo der errado, me mostra qual passo travou que eu ajudo.
