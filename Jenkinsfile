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
            // Corrigir URLs localhost
            sh "find . -type f -exec sed -i 's|http://localhost:5173|https://cmartins.pt|g' {} +"

            // Continuar com o build
            sh 'cp .env.jenkins .env'
            sh 'composer install --no-dev --prefer-dist'
        }
    }
}
        stage('Build Vue') {
            steps {
                dir('vue/Smart4Finances') {
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
                    sh "rsync -avz --delete ./ ${LARAVEL_DIR}"
                }
            }
        }
        stage('Deploy Vue') {
            steps {
                dir('vue/Smart4Finances') {
                    sh "rsync -avz --delete dist/ ${VUE_DIR}"
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
