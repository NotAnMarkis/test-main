pipeline {
    agent any

    environment {
        DOCKER_IMAGE = 'notanmarkis2/laravel-app'
        DOCKER_CREDENTIALS_ID = 'dockerhubcred'
        BUILD_NUMBER = "${env.BUILD_ID}"
    }

    stages {
        stage('Checkout Code') {
            steps {
                git branch: 'main', 
                url: 'https://github.com/notanmarkis/test-main.git'
            }
        }

        stage('Setup Environment') {
            steps {
                script {
                    // Копируем production .env
                    sh 'cp .env.production .env'
                    sh 'composer install --ignore-platform-req=ext-dom --ignore-platform-req=ext-xml --ignore-platform-req=ext-xmlwriter'
                    
                    // Генерируем APP_KEY если нужно
                    sh 'docker run --rm -v $(pwd):/app -w /app php:8.2-cli php artisan key:generate --force --no-interaction'
                }
            }
        }

        stage('Build Docker Images') {
            steps {
                script {
                    // Собираем образ приложения
                    docker.build("${env.DOCKER_IMAGE}:${env.BUILD_NUMBER}")
                }
            }
        }

        stage('Run Tests') {
            steps {
                script {
                    // Запускаем контейнер для тестов
                    docker.image("${env.DOCKER_IMAGE}:${env.BUILD_NUMBER}").inside('--network=host') {
                        // Устанавливаем зависимости
                        sh 'composer install --no-dev --optimize-autoloader'
                        
                        // Запускаем миграции и тесты
                        sh 'php artisan migrate --force'
                        
                    }
                }
            }
        }

        stage('Push to Docker Hub') {
            steps {
                script {
                    docker.withRegistry('https://index.docker.io/v1/', "${env.DOCKER_CREDENTIALS_ID}") {
                        docker.image("${env.DOCKER_IMAGE}:${env.BUILD_NUMBER}").push()
                    }
                }
            }
        }

        stage('Deploy to Production') {
            steps {
                script {
                    // Останавливаем и удаляем старые контейнеры
                    sh '''
                    docker-compose down --volumes --remove-orphans || true
                    '''

                    // Запускаем новые контейнеры
                    sh '''
                    docker-compose up -d --build
                    '''

                    // Выполняем миграции и оптимизацию
                    sh '''
                    docker-compose exec -T app composer install --no-dev --optimize-autoloader
                    docker-compose exec app php artisan optimize:clear
                    docker-compose exec app php artisan optimize
                    docker-compose exec app php artisan migrate --force
                    '''
                }
            }
        }
    }

    post {
        success {
            echo ' Laravel application deployed successfully!'
            echo ' Application URL: http://your-server-ip'
        }
        failure {
            echo ' Deployment failed!'
            // Можно добавить уведомления
        }
    }
}
