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
                    // Copia o ficheiro de ambiente adequado
                    sh 'cp .env.jenkins .env'
                    sh 'composer install --no-dev --prefer-dist'
                    // Removemos a migração, já que tens as tabelas criadas
                }
            }
        }
        stage('Build Vue') {
            steps {
                dir('vue/Smart4Finances') {
                    // Define as variáveis de ambiente para o Vite
                    sh 'echo VITE_API_DOMAIN=https://laravel.cmartins.pt > .env'
                    sh 'echo VITE_WS_CONNECTION=https://laravel.cmartins.pt >> .env'
                    sh 'npm install'
                    sh 'npm run build'
                }
            }
        }
        stage('Deploy Laravel') {
            steps {
                dir('laravel/Smart4Finances') {
                    // Copia o código para a pasta de deploy
                    sh 'rsync -avz --delete --no-group --no-owner ./ ${LARAVEL_DIR}'
                    // Ajusta as permissões para que o servidor web possa escrever
                    sh 'chown -R www-data:www-data ${LARAVEL_DIR}/storage ${LARAVEL_DIR}/bootstrap/cache'
                    sh 'chmod -R 775 ${LARAVEL_DIR}/storage ${LARAVEL_DIR}/bootstrap/cache'
                }
            }
        }
        stage('Deploy Vue') {
            steps {
                dir('vue/Smart4Finances') {
                    sh 'rsync -avz --delete --no-group --no-owner dist/ ${VUE_DIR}'
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
