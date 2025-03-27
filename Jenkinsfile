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
                sudo rsync -avz --delete ./ ${LARAVEL_DIR}
                sudo chown -R www-data:www-data ${LARAVEL_DIR}
                sudo chmod -R 755 ${LARAVEL_DIR}
                sudo chmod -R 775 ${LARAVEL_DIR}/storage ${LARAVEL_DIR}/bootstrap/cache
            """
        }
    }
}

        stage('Deploy Vue') {
    steps {
        dir('vue/Smart4Finances') {
            sh """
                rsync -avz --delete dist/ ${VUE_DIR}
                sudo chown -R www-data:www-data ${VUE_DIR}
                sudo chmod -R 755 ${VUE_DIR}
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
