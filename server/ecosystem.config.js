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