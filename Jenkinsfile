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
    }

    environment {
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

        stage('Deploy VPS') {
            when {
                expression { params.PUSH_DOCKER }
            }
            steps {
                withCredentials([sshUserPrivateKey(
                    credentialsId: 'ssh-server-credentials',
                    keyFileVariable: 'SSH_KEY',
                    usernameVariable: 'SSH_USER'
                )]) {
                    sh """
                    ssh -o StrictHostKeyChecking=no -i "\$SSH_KEY" "\$SSH_USER@vps116047" '
                      set -e
                      cd ${OLIVEAPPS_DIR}
                      sudo docker pull ${DOCKER_IMAGE}:latest
                      sudo docker compose up -d app-credit
                    '
                    """
                }
            }
        }
    }

    post {
        success {
            echo "Image ${DOCKER_IMAGE}:${DOCKER_TAG} (${env.GIT_SHA}) construite et déployée sur le VPS."
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
