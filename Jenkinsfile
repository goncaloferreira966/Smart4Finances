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
                    sh 'cp .env.jenkins .env'
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
                        rsync -az --no-perms --no-owner --no-group \
                            --chmod=Du=rwx,Dg=rx,Do=rx,Fu=rw,Fg=r,Fo=r \
                            --delete \
                            --exclude=storage/app/public/receipts/ \
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
