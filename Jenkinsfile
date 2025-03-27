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
                    // adiciona as variáveis ao .env antes do build
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
                    sh """
                        sudo chown -R 1000:1000 ${LARAVEL_DIR} || true
                        sudo chmod -R 775 ${LARAVEL_DIR} || true
                        rsync -avz --delete ./ ${LARAVEL_DIR}
                    """
                }
            }
        }
        stage('Deploy Vue') {
            steps {
                dir('vue/Smart4Finances') {
                    sh """
                        sudo chown -R 1000:1000 ${VUE_DIR} || true
                        sudo chmod -R 775 ${VUE_DIR} || true
                        rsync -avz --delete dist/ ${VUE_DIR}
                    """
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
