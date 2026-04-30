#!/bin/bash
# Start the PHP development server for Foros PSME

PORT=${1:-8000}
HOST="127.0.0.1"

echo "🚀 Iniciando servidor en http://$HOST:$PORT"
echo "📝 Router: public/index.php"
echo ""
echo "Credenciales de demo:"
echo "  Admin:    admin@psme.local / Admin123*"
echo "  Asociado: asociado@psme.local / Asociado123*"
echo "  Usuario:  usuario@psme.local / Usuario123*"
echo ""

cd "$(dirname "$0")"
php -S $HOST:$PORT public/index.php
