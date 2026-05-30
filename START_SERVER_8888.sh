#!/bin/bash

echo "========================================"
echo "Peace Seafood - Starting Server"
echo "Port: 8888"
echo "========================================"
echo ""

# Check if port 8888 is already in use
if lsof -Pi :8888 -sTCP:LISTEN -t >/dev/null ; then
    echo "WARNING: Port 8888 is already in use!"
    echo ""
    echo "To kill the process using port 8888:"
    echo "  lsof -ti:8888 | xargs kill -9"
    echo ""
    exit 1
fi

echo "Starting PHP Built-in Server..."
echo ""
echo "Server will be available at:"
echo "  http://localhost:8888/peace_seafood/"
echo ""
echo "Press Ctrl+C to stop the server"
echo "========================================"
echo ""

# Get script directory
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$DIR"

php -S localhost:8888 -t public
