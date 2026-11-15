> Pesquisa histórica: o projeto atual usa **mysqli**, com conexão em `infra/conexao.php` e consultas preparadas. Os exemplos PDO abaixo são material de estudo e não instruções de instalação.

-------------------------------------------------------PHP Data Objects-------------------------------------------------------------------

O que é o PDO:
R) O PDO é uma extensão do PHP para ter acesso ao banco de dados, sendo totalmente orienteado a objetos, possui uma interface leve para acessar o banco de dados no php

Para que ele é utilizado no PHP:
R) A principal função dele e você conseguir fazer uma busca de usuário com prepared statements assim aumentando a segurança dessa ação e evitando risco de SQL Injection

Como funciona uma conexão utilizando PDO:
R)Você utiliza ele dentro da conexao.php dai você faz o código por exemplo
<?php
$dsn = 'mysql:host=localhost;dbname=nome_do_banco;charset=utf8mb4';
$usuario = 'root';
$senha = '';

try {
    $pdo = new PDO($dsn, $usuario, $senha);
    
    // Configura o PDO para lançar exceções em caso de erros
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conexão realizada com sucesso!";
} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
}
?>
O PDO estabelece a conexão segura com o banco de dados MySQL, executa o comando de leitura na tabela "usuarios" e converte as linhas encontradas em objetos PHP para que possam ser exibidos na lista HTML.



Quais são suas principais características:
R) Ele utiliza as mesma funções para criar diferentes bancos de dados, organizado por meio de classes estruturadas, previni ataques SQLInjection, permiete capturar erros de conexão do banco de dados, suporta controle total de transações.

Diferenças entre PDO e MySQLi:
R) O  mySQLi apenas funciona para mySQL diferente do PDO que permite a conexão com mais de 12 bancos de dados, utiliza estilo de orientação a objetos e também a alteralçao do banco que é muito mais facilitado no PDO por necessitar de menos quantidade de mudanças na estrutura 

Vantagens e desvantagens de utilizar PDO:

R)Vantagens: Você pode trocar de banco de dados mudando apenas a string de conexão, sem reescrever o código das consultas.Suporta prepared statements  que protegem a aplicação contra ataques de Injeção de SQL.Permite nomear os parâmetros nas consultas, deixando o código mais limpo e fácil de manter do que o uso de pontos de interrogação.Oferece suporte nativo a transações via comandos como commit ou rollback. 
Desvantagens: Exige conhecimento prévio de Programação Orientada a Objetos (POO), o que pode dificultar o início para desenvolvedores novatos.O PDO não traduz ou interpreta instruções SQL complexas; ele apenas repassa os comandos para o driver específico do banco.Requer que o driver específico do banco de dados esteja instalado e habilitado no servidor PHP. 


O que são Prepared Statements e por que são importantes:
R)Prepared statements são modelos de consultas de banco de dados que separam a estrutura do código SQL dos valores enviados pelo usuário, garantindo máxima segurança e eficiência.
Normalmente, quando você envia uma consulta para o banco, ele lê o texto inteiro de uma só vez (código e dados juntos). Com o prepared statement, o processo muda:

Você envia o modelo da consulta para o banco de dados.
O banco de dados analisa, compila e otimiza esse modelo.
Você envia os dados (parâmetros) separadamente para preencher o modelo.
Por que são tão importantes?

Proteção contra SQL Injection

Esta é a principal vantagem. O SQL Injection é um ataque onde um invasor digita códigos maliciosos nos campos de texto (como o campo de login) para roubar ou apagar dados.
Com o Prepared statement, o banco de dados trata o texto digitado pelo usuário sempre como dado puro, nunca como um comando executável. O código malicioso perde o efeito e é neutralizado automaticamente.

2. Melhora no Desempenho
Quando você faz a mesma consulta várias vezes seguidas (por exemplo, inserindo 1.000 produtos), o banco de dados precisa "entender" o comando a cada repetição.
Com o Prepared statement, o banco compila a consulta uma única vez e apenas troca os valores nas execuções seguintes. Isso economiza processamento e acelera o sistema.

3. Código mais limpo e organizado
Separar o código SQL dos dados deixa o programa mais fácil de ler, manter e corrigir no futuro.


Em quais situações o PDO pode ser uma boa escolha:
R)Quando você pode mudar de banco de dados no futuro: Se hoje você usa MySQL e amanhã precisa mudar para PostgreSQL ou SQLite, o PDO exige pouquíssimas alterações no seu código.
Quando a segurança é prioridade: Ele usa prepared statements (consultas preparadas), que separam os dados digitados pelo usuário do comando do banco, bloqueando o ataque de SQL Injection (invasão por formulários).
Quando o projeto usa Orientação a Objetos: O PDO foi desenhado para retornar os dados do banco diretamente em formato de objetos, encaixando perfeitamente em códigos modernos.
Quando você acessa mais de um banco ao mesmo tempo: Se o seu sistema precisa ler dados do MySQL e gravar logs no SQLite, você usa os mesmos comandos básicos para ambos.




