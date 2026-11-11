"use strict";


const formulario = document.getElementById("formRota");
const inputOrigem = document.getElementById("origem");
const inputDestino = document.getElementById("destino");
const inputPesquisa = document.getElementById("pesquisarRota");
const listaRotas = document.getElementById("listaRotas");
const estadoVazio = document.getElementById("estadoVazio");
const mensagemFormulario = document.getElementById("mensagemFormulario");

const itensRota = Array.from(listaRotas.querySelectorAll(".rota"));

function normalizarTexto(texto) {
    return texto
        .normalize("NFD")
        .replace(/[̀-ͯ]/g, "")
        .toLowerCase()
        .trim();
}

function mostrarMensagem(texto, tipo = "") {
    if (!mensagemFormulario) {
        return;
    }

    mensagemFormulario.textContent = texto;
    mensagemFormulario.className = `mensagem ${tipo}`.trim();

    window.clearTimeout(mostrarMensagem.temporizador);

    if (texto) {
        mostrarMensagem.temporizador = window.setTimeout(() => {
            mensagemFormulario.textContent = "";
            mensagemFormulario.className = "mensagem";
        }, 3500);
    }
}

function filtrarRotas(filtro = "") {
    const termo = normalizarTexto(filtro);
    let quantidadeExibida = 0;

    itensRota.forEach((item) => {
        const corresponde = normalizarTexto(item.textContent).includes(termo);
        item.style.display = corresponde ? "" : "none";

        if (corresponde) {
            quantidadeExibida++;
        }
    });

    const semResultados = quantidadeExibida === 0;
    estadoVazio.classList.toggle("oculto", !semResultados);

    const tituloVazio = estadoVazio.querySelector("h3");

    if (termo && semResultados) {
        tituloVazio.textContent = "Nenhuma rota encontrada";
    } else {
        tituloVazio.textContent = "Nenhuma rota cadastrada";
    }

}

if (formulario) {
    formulario.addEventListener("submit", (evento) => {
        if (normalizarTexto(inputOrigem.value) === normalizarTexto(inputDestino.value)) {
            evento.preventDefault();
            mostrarMensagem("A origem e o destino precisam ser diferentes.", "erro");
            inputDestino.focus();
        }
    });
}

inputPesquisa.addEventListener("input", () => {
    filtrarRotas(inputPesquisa.value);
});

listaRotas.addEventListener("submit", (evento) => {
    const formExcluir = evento.target.closest(".form-excluir");

    if (formExcluir && !window.confirm(`Deseja excluir a rota ${formExcluir.dataset.trajeto}?`)) {
        evento.preventDefault();
    }
});

if (mensagemFormulario && mensagemFormulario.textContent.trim()) {
    mostrarMensagem(mensagemFormulario.textContent.trim(), mensagemFormulario.classList.contains("erro") ? "erro" : "sucesso");
}

filtrarRotas();
