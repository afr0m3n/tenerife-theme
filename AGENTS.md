# Tenerife WordPress Theme

Toto repo obsahuje pouze vlastní WordPress šablonu `tenerife`.

## Pravidla práce
- Pracuj pouze uvnitř této šablony.
- Neupravuj WordPress core.
- Nepřidávej pluginy bez schválení.
- Necommituj databázi, uploady, cache, node_modules ani vendor.
- Šablona je classic WordPress theme.
- Testovací WordPress běží v Dockeru ve stacku `/srv/stacks/tenerife-wp-dev`.
- Šablona je do WordPress kontejneru připojena jako `/var/www/html/wp-content/themes/tenerife`.

## Kontroly
Po úpravách zkontroluj:
- `git diff`
- základní PHP syntaxi změněných PHP souborů
- vzhled ve WordPressu na DEV instanci

## Lokální/dev stack
Theme path:
`/srv/apps/tenerife-theme`

Docker stack:
`/srv/stacks/tenerife-wp-dev`

Šablona je v kontejneru připojena jako:
`/var/www/html/wp-content/themes/tenerife`