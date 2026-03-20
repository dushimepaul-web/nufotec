# Créer le script
cat > /home/nufotec/check-server.sh << 'EOF'
#!/bin/bash
if ! pm2 describe nufotec-server > /dev/null 2>&1; then
    cd /home/nufotec/public_html/server && pm2 start index.js --name "nufotec-server"
fi
EOF

chmod +x /home/nufotec/check-server.sh





crontab -e


souvgarder asc :wq 

*/5 * * * * /home/nufotec/check-server.sh



Créez le fichier ecosystem.config.js dans /home/nufotec/public_html/server/ :
JavaScript
Copy

module.exports = {
  apps: [{
    name: 'nufotec-server',
    script: './index.js',
    instances: 1,
    autorestart: true,
    watch: false,
    max_memory_restart: '500M',
    restart_delay: 3000,
    max_restarts: 10,
    min_uptime: '10s',
    env: {
      NODE_ENV: 'production'
    },
    error_file: '/home/nufotec/public_html/server/logs/err.log',
    out_file: '/home/nufotec/public_html/server/logs/out.log',
    log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
    // Redémarrage automatique en cas de crash
    exp_backoff_restart_delay: 100
  }]
};




pm2 delete nufotec-server  # Supprimer l'ancien
pm2 start ecosystem.config.js
pm2 save

# Voir les logs en temps réel
pm2 logs nufotec-server

# Voir le statut détaillé
pm2 show nufotec-server

# Redémarrer manuellement si besoin
pm2 restart nufotec-server