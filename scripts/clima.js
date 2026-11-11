"use strict";

const temperatura = document.getElementById("clima-temperatura");

async function carregarClima() {
    if (!temperatura) return;
    const painel = temperatura.closest("[role='status']");
    const controle = new AbortController();
    const limite = window.setTimeout(() => controle.abort(), 10000);
    painel.setAttribute("aria-busy", "true");
    temperatura.classList.add("clima-status");
    temperatura.textContent = "Consultando…";

    try {
        const url = "https://api.open-meteo.com/v1/forecast" +
            "?latitude=-26.3044&longitude=-48.8487" +
            "&current=temperature_2m&temperature_unit=celsius" +
            "&timezone=America%2FSao_Paulo";
        const resposta = await fetch(url, { signal: controle.signal });
        if (!resposta.ok) throw new Error("Falha na consulta meteorológica");
        const dados = await resposta.json();
        const valor = dados.current?.temperature_2m;
        if (typeof valor !== "number" || !Number.isFinite(valor)) {
            throw new Error("Temperatura não informada pela API");
        }
        temperatura.textContent = new Intl.NumberFormat("pt-BR", { maximumFractionDigits: 0 }).format(valor) + " °C";
        temperatura.classList.remove("clima-status");
    } catch {
        temperatura.textContent = "Clima indisponível";
    } finally {
        window.clearTimeout(limite);
        painel.setAttribute("aria-busy", "false");
    }
}

carregarClima();
