# Rail View — Ferrorama

Protótipo acadêmico para gerenciar trens, sensores, rotas e usuários. A versão atual oferece cadastros e consultas; a integração física de sensores e o monitoramento em tempo real fazem parte da proposta futura.

## Instalação local

1. Instale XAMPP com PHP 8.1 ou superior, extensões mysqli/mysqlnd e mbstring, Apache e **MariaDB 10.4 ou superior**. O SQL usa comandos de migração específicos do MariaDB; não é garantido para MySQL.
2. Baixe o repositório completo e coloque a pasta em `C:\xampp\htdocs\RailView-SA`. Preserve `Assets/`, `scripts/`, `infra/`, `public/` e `index.html`.
3. Ligue Apache e MariaDB no painel do XAMPP. Confira se a porta do banco é 3306.
4. Abra o phpMyAdmin e importe `database/SA_ferrorama.sql`. O script cria `rail_view_db`, tabelas, perfis e dados fictícios.
5. Abra `http://localhost/RailView-SA/public/login.php` ou a página inicial em `http://localhost/RailView-SA/`.

O SQL pode ser reimportado sem recriar os exemplos existentes. Não redefine senhas de contas já cadastradas. Faça backup antes de importar sobre um banco existente: há alterações de estrutura e normalização de vínculos antigos.

A conexão está centralizada em `infra/conexao.php`, usando **mysqli** e utf8mb4. Os padrões são localhost, porta 3306, usuário root, senha vazia e banco rail_view_db, exclusivamente para o ambiente local do XAMPP. É possível configurar `RAILVIEW_DB_HOST`, `RAILVIEW_DB_PORT`, `RAILVIEW_DB_USER`, `RAILVIEW_DB_PASSWORD` e `RAILVIEW_DB_NAME` no ambiente do processo PHP/Apache; reinicie o serviço após alterar. Não há carregamento automático de arquivo .env. Não publique uma instalação com root e senha vazia.

## Demonstração

Contas fictícias criadas pelo SQL, com senha pública **`password`** e hash bcrypt armazenado no banco. Não são credenciais pessoais e não devem ser usadas em produção.

| Nome | Email | Perfil |
|---|---|---|
| Admin Demo | admin@demo.test | Administrador |
| Funcionario Demo | funcionario@demo.test | Funcionário |
| Usuario Demo | usuario@demo.test | Usuário |

O login aceita email ou nome completo. Há dois trens, dois sensores e duas rotas de exemplo com vínculos coerentes, sem depender dos IDs de outras instalações.

Em bancos importados a partir da versão antiga, a importação não apaga nem altera contas pessoais ou senhas antigas. Use um banco local limpo para a demonstração ou revise essas contas pela interface administrativa. Não utilize a antiga conta pessoal como acesso de apresentação.

## Perfis e permissões

| Operação | Administrador | Funcionário | Usuário |
|---|---|---|---|
| Consultar trens, sensores, rotas e alertas | Sim | Sim | Sim |
| Configurar limites e registrar leituras | Sim | Não | Não |
| Cadastrar, editar e excluir trens, sensores e rotas | Sim | Não | Não |
| Listar e administrar outros usuários | Sim | Não | Não |
| Consultar, editar e excluir a própria conta | Sim, respeitando o último admin | Sim | Sim |

Funcionário e Usuário têm as mesmas permissões nesta versão. Isso diverge de RN11–RN15 do documento original, que atribui gestão operacional a Funcionário; essa mudança de escopo precisa ser validada pela equipe. As verificações ficam no backend; ocultar menus é apenas parte da interface. O cadastro de novas contas é administrativo.

O último administrador não pode ser excluído nem rebaixado. Um trem com sensores ou rotas vinculados não pode ser excluído. A exclusão de rotas apaga os vínculos correspondentes em Historico_Rota.

## Tecnologias e estrutura

PHP, MariaDB, HTML, CSS, JavaScript e Bootstrap. Fontes, ícones, Bootstrap, consulta de CEP via ViaCEP e clima via Open-Meteo dependem de internet.

| Caminho | Responsabilidade |
|---|---|
| `index.html` | Apresentação e entrada |
| `public/` | Login, perfil e telas de gestão |
| `infra/` | Conexão mysqli, autenticação, mensagens e navegação |
| `database/SA_ferrorama.sql` | Estrutura, migrações e dados fictícios |
| `Assets/` | Estilos, logos, imagens e vídeos |
| `scripts/` | JavaScript e verificação CLI dos hashes demo |
| `docs/` | Requisitos, diagramas, roteiro e evidências |
| `MANUAL.md` | Manual rápido |

## Limitações conhecidas

- Sensor pertence a um trem. Sua associação às rotas é indireta: Sensor → Trem → Historico_Rota → Rota. Isso precisa ser explicado na apresentação e validado com o critério 3.5.
- Há registro manual de leituras, limites máximos por sensor e alertas automáticos por excesso ou falha, com histórico em public/alertas.php. Não há integração IoT física. Manutencao continua sem fluxo PHP. A tela mostra as últimas 100 leituras e os últimos 100 alertas; o banco preserva os anteriores até a exclusão do sensor.
- Não há relatório operacional automatizado ou manutenção preditiva. Textos e campos de relatório não demonstram essas funcionalidades.
- Historico_Rota é usado como vínculo de cadastro; a edição de rota atualiza o trem vinculado. Não constitui trilha imutável de viagens.
- Responsividade, fluxos completos e funcionamento no computador da bancada ainda precisam de execução e evidências.

## Uso, testes e capturas

Consulte o [manual rápido](MANUAL.md), o [roteiro de testes](docs/roteiro_de_testes.md), o [teste externo](docs/teste_externo.md) e a [lista de entrega](docs/entrega.md).

As capturas reais ainda estão pendentes. A lista de telas e nomes dos arquivos está em [docs/evidencias](docs/evidencias/README.md). Não há teste de navegador concluído registrado nesta revisão.

Com PHP instalado, execute na raiz:

```powershell
Get-ChildItem infra,public,scripts -Filter *.php -Recurse | ForEach-Object { php -l $_.FullName }
php scripts/verificar_demo.php
php scripts/verificar_alertas.php
```

A verificação dos hashes não depende do banco. Para conferir a reimportação, importe o SQL duas vezes em um banco de teste e compare a quantidade de contas e registros demo, depois valide os três logins. Não considere análise estática equivalente a teste funcional.

## Equipe e contribuições

Os requisitos originais identificam Beatriz Fröhlich, Carlos Eduardo Buttke, Caio Henrique Leite, Nordeley Medeiros Corradini e Lucas Onofre Senczuck. **Pendente:** confirmar a contribuição individual de cada integrante. A autoria do Git não permite atribuir todas as tarefas com segurança.

## Organização e versão

[Repositório](https://github.com/ButtKeC0de/RailView-SA) · [Kanban](https://github.com/users/ButtKeC0de/projects/2) · [Documentação](docs/README.md)

A preparação da versão v1.0.0 está descrita em [docs/entrega.md](docs/entrega.md). A existência de tag e release deve ser conferida no GitHub; documentação de preparação não significa publicação realizada.

Licença: [MIT](LICENSE).

