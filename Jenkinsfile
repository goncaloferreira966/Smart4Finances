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

        stage('Deploy Laravel') {
            steps {
                dir('laravel/Smart4Finances') {
                    sh 'chown -R jenkins:jenkins ./storage ./bootstrap/cache || true'
                    sh '''
                        rsync -az --no-perms --no-owner --no-group \
                        --delete --chmod=Du=rwx,Dg=rx,Do=rx,Fu=rw,Fg=r,Fo=r \
                        ./ ${LARAVEL_DIR}
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
