# Como usar o Enxoval

Guia passo a passo completo.

---

## 1. Pré-requisitos

Você precisa ter o **.NET 10** instalado no computador.

Teste se já tem:
```powershell
dotnet --version
```

Se não tiver, baixe em: https://dotnet.microsoft.com/download

---

## 2. Rodar o servidor

Abra o **PowerShell** e vá até a pasta do projeto:

```powershell
cd C:\Users\guilh\source\repos\enxoval\Enxoval.Web
```

Rode o servidor:

```powershell
dotnet run
```

Você vai ver algo tipo:

```
Now listening on http://localhost:5000
Now listening on http://192.168.1.100:5000
```

**Deixe esse terminal aberto** — se fechar, o site para.

Acesse no seu navegador: http://localhost:5000

---

## 3. Testar na rede local (mesmo WiFi)

No celular da sua parceira, abra o navegador e digite o IP que apareceu no terminal.

Exemplo: `http://192.168.1.100:5000`

Se funcionar, os dois já veem a mesma lista.

> **Se não funcionar:** o firewall do Windows pode estar bloqueando a porta 5000.
> Solução rápida: desliga o firewall por 1 minuto pra testar, ou permite a porta no Defender.

---

## 4. Colocar na internet (grátis, sem cartão)

Você vai usar o **Cloudflare Tunnel** — cria um link público sem precisar de cartão de crédito.

### 4.1 Baixar o Cloudflared

1. Acesse: https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe
2. O download vai começar automaticamente
3. Crie uma pasta `C:\cloudflared` no seu computador
4. Copie o arquivo baixado para dentro dessa pasta
5. Renomeie o arquivo para `cloudflared.exe` (se já não estiver com esse nome)

### 4.2 Criar o túnel

Deixe o terminal do `dotnet run` rodando. **Abra um segundo PowerShell** e digite:

```powershell
C:\cloudflared\cloudflared.exe tunnel --url http://localhost:5000
```

Aguarde alguns segundos. Quando aparecer uma linha assim:

```
https://palavras-aleatorias.trycloudflare.com
```

Pronto! Esse é o link público do seu site. Qualquer pessoa com esse link consegue acessar.

### 4.3 Manter funcionando

Você precisa manter **os dois terminais abertos**:

| Terminal | O que roda | O que faz |
|----------|-----------|-----------|
| 1 | `dotnet run` | Servidor do site |
| 2 | `cloudflared tunnel...` | Túnel pra internet |

Se fechar qualquer um, o site cai. É só rodar de novo.

> O link muda toda vez que você rodar o tunnel. Pra ter um link fixo (ex: `meulista.com`), precisa configurar um domínio — me avisa se quiser fazer isso depois.

---

## 5. Usar no dia a dia

Sempre que quiser usar o site:

1. Abre o PowerShell e roda `dotnet run`
2. Abre outro PowerShell e roda o cloudflared
3. Pega o link que apareceu e manda pra quem precisar

Pra parar: aperta `Ctrl+C` em cada terminal.

---

## Dúvidas?

Me chama aqui no chat que eu ajudo.
