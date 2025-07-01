pipeline {
    agent any

    environment {
        LARAVEL_DIR = '/var/www/laravel.cmartins.pt/html'
        VUE_DIR = '/var/www/cmartins.pt/html'
    }

    stages {
        stage('Build Laravel') {
            steps {
                dir('laravel/Smart4Finances') {
                    sh '''
                        # Criar arquivo .env para produção
                        cat > .env << 'EOF'
APP_NAME=Smart4Finances
APP_ENV=production
APP_KEY=base64:49oFwCgptJID0XeuwecND5d19napg9yaD9ooqXrViGk=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://laravel.cmartins.pt
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
PHP_CLI_SERVER_WORKERS=4
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=smart4finances_prod
DB_USERNAME=smart4finances_user
DB_PASSWORD=your_production_password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.cmartins.pt
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=Smart4Finances@gmail.com
MAIL_PASSWORD=totgpjbusbqpltyo
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=Smart4Finances@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

# Configurações específicas para CORS e SPA
SANCTUM_STATEFUL_DOMAINS=cmartins.pt,laravel.cmartins.pt
SPA_URL=https://cmartins.pt
CORS_ALLOWED_ORIGINS=https://cmartins.pt
EOF
                    '''
                    sh 'composer install --no-dev --prefer-dist'
                }
            }
        }

        stage('Build Vue') {
            steps {
                dir('vue/Smart4Finances') {
                    sh 'npm install'
                    sh 'echo VITE_API_DOMAIN=https://laravel.cmartins.pt > .env'
                    sh 'echo VITE_WS_CONNECTION=https://laravel.cmartins.pt >> .env'
                    sh 'npm run build'
                }
            }
        }

        stage('Corrigir URLs no Laravel') {
            steps {
                dir('laravel/Smart4Finances') {
                    sh '''
                        echo "🔁 A substituir http://localhost:5173 por https://cmartins.pt no Laravel..."
                        grep -rl 'http://localhost:5173' . | xargs sed -i 's|http://localhost:5173|https://cmartins.pt|g'
                    '''
                }
            }
        }

        stage('Deploy Laravel') {
            steps {
                dir('laravel/Smart4Finances') {
                    sh '''
                        chown -R jenkins:jenkins ./storage ./bootstrap/cache || true
                        # Ajustar permissões no destino antes do rsync
                        sudo chown -R jenkins:jenkins /var/www/laravel.cmartins.pt/html/storage/app/public/photos/ || true
                        sudo chmod -R 755 /var/www/laravel.cmartins.pt/html/storage/app/public/photos/ || true
                        
                        rsync -az --no-perms --no-owner --no-group \
                            --chmod=Du=rwx,Dg=rx,Do=rx,Fu=rw,Fg=r,Fo=r \
                            --delete \
                            --exclude=storage/app/public/receipts/ \
                            --exclude=storage/app/public/photos/ \
                            ./ /var/www/laravel.cmartins.pt/html
                        
                        sudo /usr/local/bin/refresh_passport_keys.sh
                        cd /var/www/laravel.cmartins.pt/html && php artisan storage:link
                    '''
                }
            }
        }

        stage('Deploy Vue') {
            steps {
                dir('vue/Smart4Finances') {
                    sh '''
                        rsync -az --no-perms --no-owner --no-group \
                        --delete --chmod=Du=rwx,Dg=rx,Do=rx,Fu=rw,Fg=r,Fo=r \
                        dist/ ${VUE_DIR}
                    '''
                }
            }
        }
    }

    post {
        success {
            echo '✅ Deploy concluído com sucesso!'
        }
        failure {
            echo '❌ Deploy falhou!'
        }
    }
}
