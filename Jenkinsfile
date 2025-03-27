pipeline {
    agent any
    environment {
        // Define os caminhos de deploy para backend e frontend
        LARAVEL_DIR = '/var/www/laravel.cmartins.pt/html'
        VUE_DIR = '/var/www/cmartins.pt/html'
    }
    stages {
        stage('Build Laravel') {
            steps {
                // Instala dependências do Laravel e executa migrações
                sh 'composer install --no-dev --prefer-dist'
                sh 'php artisan migrate --force'
            }
        }
        stage('Build Vue') {
            steps {
                // Instala as dependências e compila a aplicação Vue
                sh 'npm install'
                sh 'npm run build'
            }
        }
        stage('Deploy Laravel') {
            steps {
                // Copia os ficheiros do backend para o diretório de deploy
                sh "rsync -avz --delete ./ ${LARAVEL_DIR}"
            }
        }
        stage('Deploy Vue') {
            steps {
                // Copia os ficheiros compilados (geralmente na pasta 'dist') para o diretório do frontend
                sh "rsync -avz --delete dist/ ${VUE_DIR}"
            }
        }
    }
    post {
        success {
            echo 'Deploy concluído com sucesso!'
        }
        failure {
            echo 'Deploy falhou!'
        }
    }
}
