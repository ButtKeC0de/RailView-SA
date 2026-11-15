# Rail View v1.0.0

Protótipo acadêmico de gestão ferroviária com login, perfis, trens, sensores, rotas e usuários.

- README e manual rápido com instalação, perfis, escopo e instruções de uso.
- Contas fictícias e carga demonstrativa reexecutável, com senhas bcrypt.
- Limites por sensor, registro manual de leituras e histórico de alertas automáticos por excesso ou falha.
- Correções de navegação e conexão configurável por variáveis de ambiente.
- Verificações automatizadas de sintaxe PHP, hashes, regras de alertas e importação dupla em MariaDB.

## Limitações e pendências

As leituras são manuais; não existe conexão física com sensores IoT. Funcionário e Usuário compartilham as permissões de consulta. Manutenção, estatísticas, relatórios semanais e trilha imutável de viagens não estão implementados. Capturas reais, teste externo, teste da estação, atualização do Kanban e atribuição das contribuições individuais permanecem pendentes e estão descritos em docs/entrega.md.

Os testes automatizados não comprovam funcionamento completo no navegador. Use somente dados e contas fictícios para demonstração local.
