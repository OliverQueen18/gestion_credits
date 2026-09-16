pipeline {

    agent any

    options {
        timestamps()
        disableConcurrentBuilds()
        skipDefaultCheckout(false)
    }

    parameters {
        booleanParam(
            name: 'PUSH_DOCKER',
            defaultValue: true,
            description: 'Pousser l’image vers Docker Hub'
        )
        booleanParam(
            name: 'DEPLOY_PROD',
            defaultValue: false,
            description: 'Sur le serveur OliveApps : docker compose pull + up app-credit'
        )
    }

    environment {
        // Doit correspondre à image: dans OliveApps docker-compose.yml
        DOCKER_IMAGE = 'oliverqueen18/gestion-credit'
        DOCKER_TAG = "${BUILD_NUMBER}"
        DOCKER_BUILDKIT = '1'
        COMPOSE_DOCKER_CLI_BUILD = '1'
        OLIVEAPPS_DIR = '/home/adminubuntu/OliveApps'
    }

    stages {

        stage('Checkout') {
            steps {
                checkout scm
                script {
                    env.GIT_SHA = sh(script: 'git rev-parse --short HEAD', returnStdout: true).trim()
                }
            }
        }

        stage('Docker Build') {
            steps {
                sh """
                docker pull ${DOCKER_IMAGE}:latest || true
                docker build \
                  --build-arg BUILDKIT_INLINE_CACHE=1 \
                  --cache-from ${DOCKER_IMAGE}:latest \
                  -t ${DOCKER_IMAGE}:${DOCKER_TAG} \
                  -t ${DOCKER_IMAGE}:${env.GIT_SHA} \
                  -t ${DOCKER_IMAGE}:latest \
                  .
                docker image inspect ${DOCKER_IMAGE}:${DOCKER_TAG} --format '{{.Id}} {{.RepoTags}}'
                """
            }
        }

        stage('Docker Push') {
            when {
                expression { params.PUSH_DOCKER }
            }
            steps {
                withCredentials([usernamePassword(
                    credentialsId: 'dockerhub-credentials',
                    usernameVariable: 'DOCKER_USER',
                    passwordVariable: 'DOCKER_PASS'
                )]) {
                    sh """
                    echo "\$DOCKER_PASS" | docker login -u "\$DOCKER_USER" --password-stdin
                    docker push ${DOCKER_IMAGE}:${DOCKER_TAG}
                    docker push ${DOCKER_IMAGE}:${env.GIT_SHA}
                    docker push ${DOCKER_IMAGE}:latest
                    """
                }
            }
        }

        stage('Deploy OliveApps') {
            when {
                expression { params.PUSH_DOCKER && params.DEPLOY_PROD }
            }
            steps {
                sshagent(credentials: ['oliveapps-ssh']) {
                    sh """
                    ssh -o StrictHostKeyChecking=no adminubuntu@olive-services.net '
                      set -e
                      cd ${OLIVEAPPS_DIR}
                      docker pull ${DOCKER_IMAGE}:latest
                      docker compose up -d app-credit
                    '
                    """
                }
            }
        }
    }

    post {
        success {
            echo "Image ${DOCKER_IMAGE}:${DOCKER_TAG} (${env.GIT_SHA}) construite."
        }
        failure {
            echo 'Pipeline Gestion de Credit échoué.'
        }
        always {
            sh 'docker logout || true'
            cleanWs(deleteDirs: true, notFailBuild: true)
        }
    }
}
