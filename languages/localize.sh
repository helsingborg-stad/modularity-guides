wp i18n make-pot . languages/modularity-guides.pot --slug='modularity-guides' --allow-root --include=source/php,templates
wp i18n update-po languages/modularity-guides.pot languages/modularity-guides-sv_SE.po --allow-root
wp i18n make-mo languages/modularity-guides-sv_SE.po languages/modularity-guides-sv_SE.mo --allow-root
