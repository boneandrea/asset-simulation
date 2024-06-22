#!/bin/bash
set -x

(cd front && npm run build)

. .env
# FRONT_DESTINATION=
# BACK_DESTINATION=
# SSH_KEY=

scp -i $SSH_KEY -r front/dist/* $FRONT_DESTINATION
scp -i $SSH_KEY back/*.php $BACK_DESTINATION
