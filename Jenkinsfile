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
                    // Garante que existe um .env
                    sh 'cp .env.jenkins .env || true'
                    
                    // Substitui localhost:5173 por domínio
                    sh "find . -type f -exec sed -i 's|http://localhost:5173|https://cmartins.pt|g' {} +"

                    // Instalação e build
                    sh 'composer install --no-dev --prefer-dist'
                }
            }
        }

        stage('Build Vue') {
            steps {
                dir('vue/Smart4Finances') {
                    // Injeta variáveis de ambiente no .env.local
                    sh 'echo VITE_API_DOMAIN=https://laravel.cmartins.pt > .env.local'
                    sh 'echo VITE_WS_CONNECTION=https://laravel.cmartins.pt >> .env.local'

                    // Build Vue
                    sh 'npm install'
                    sh 'npm run build'
                }
            }
        }

        stage('Deploy Laravel') {
            steps {
                dir('laravel/Smart4Finances') {
                    // Sincroniza ficheiros para o host
                    sh "rsync -avz --no-perms --no-owner --no-group --chmod=Du=rwx,Dg=rx,Do=rx,Fu=rw,Fg=r,Fo=r --delete ./ ${LARAVEL_DIR}"


                    // Corrige permissões das pastas necessárias
                    sh "chown -R www-data:www-data ${LARAVEL_DIR}/storage ${LARAVEL_DIR}/bootstrap/cache || true"
                }
            }
        }

        stage('Deploy Vue') {
            steps {
                dir('vue/Smart4Finances') {
                    // Sincroniza build Vue para o host
                    sh "rsync -avz --no-perms --no-owner --no-group --chmod=Du=rwx,Dg=rx,Do=rx,Fu=rw,Fg=r,Fo=r --delete dist/ ${VUE_DIR}"

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
