# Manual rápido — Rail View

## Acesso

Com Apache e MariaDB ligados e o banco importado conforme o [README](README.md), abra `http://localhost/RailView-SA/public/login.php`.

As contas abaixo são fictícias, exclusivamente para demonstração local. A senha de todas é `password`.

| Perfil | Email |
|---|---|
| Administrador | admin@demo.test |
| Funcionário | funcionario@demo.test |
| Usuário | usuario@demo.test |

Digite o email e a senha e clique em Entrar. O login também aceita o nome completo da conta. Use Sair para encerrar a sessão.

## Navegação e consultas

O menu lateral dá acesso a Início, Rotas, Trens e Sensores. Clique no perfil para consultar seus dados. Funcionário e Usuário têm as mesmas permissões nesta versão: consultas e gerenciamento da própria conta.

## Administração

1. Entre como administrador e abra Trens. Cadastre um trem antes de cadastrar seus sensores ou rotas.
2. Em Sensores, informe tipo, localização, operadora, status e o trem associado. O status é informado manualmente; não representa uma leitura de equipamento.
3. Em Rotas, escolha o trem e preencha origem, destino e operadora. Origem e destino precisam ser diferentes.
4. Nas listagens, use Editar para alterar os dados e Excluir para removê-los. Confirme a exclusão somente após conferir o registro.
5. Em Gerenciar usuários, cadastre contas com o perfil apropriado. A lista possui o botão Início para voltar à home.

Um trem com sensores ou rotas vinculados não pode ser excluído. Remova os vínculos primeiro. O último administrador não pode ser excluído nem perder seu perfil. Excluir uma rota remove também seus vínculos em Historico_Rota.

## Sensores e rotas

A associação é indireta: Sensor → Trem → Historico_Rota → Rota. Não há cadastro de sensor exclusivo de uma rota. Abra Alertas pelo menu lateral. O administrador pode configurar o máximo e a unidade de cada sensor e registrar leituras de demonstração. Uma leitura acima do máximo ou marcada como falha gera um alerta automaticamente. Valor igual ao máximo não gera alerta. Limite vazio desativa apenas a comparação numérica. Funcionário e Usuário consultam o histórico. Não há conexão física IoT nem gestão de manutenção.

## Problemas frequentes

| Sintoma | Como conferir |
|---|---|
| Erro de conexão | Ligue o MariaDB, confira a porta 3306 e a configuração descrita no README. |
| Login recusado | Confira o email e a senha fictícios; importe o SQL em um banco local limpo. Reimportar não redefine senhas existentes. |
| Acesso negado | Confira se o perfil tem permissão. A administração exige Administrador. |
| Página não encontrada | A pasta deve se chamar RailView-SA dentro de htdocs; confira maiúsculas e minúsculas do caminho. |
| Ícones, fontes ou clima ausentes | Esses recursos dependem de acesso à internet. |

## Capturas de tela

**Pendentes de execução real.** O manual ainda precisa das capturas de login, home, trens, sensores, rotas, usuários, alertas e tela pequena. Siga o [plano de evidências](docs/evidencias/README.md). Nenhuma imagem simulada serve como comprovação de funcionamento.

## Suporte

Registre problemas nas [issues do repositório](https://github.com/ButtKeC0de/RailView-SA/issues), com passos para reproduzir, resultado esperado e observado. Não publique dados pessoais, cookies nem senhas reais.
