#!/bin/bash

KINSTA_USER=pvdyoungmakers
KINSTA_IP=35.193.232.176
KINSTA_PORT=22433
DESTINATION=public


if [ -z ${DESTINATION+x} ]; then
    echo "No destination specified; nothing done."
    exit 1
fi

echo "destination = $KINSTA_IP:~/$DESTINATION"

NODE_ENV=staging npm run build

# NOTE: Uncomment this line to deploy the theme
scp -P $KINSTA_PORT -r ./wp-content/themes/custom/. $KINSTA_USER@$KINSTA_IP:./$DESTINATION/wp-content/themes/custom

# NOTE: Uncomment this line to deploy plugins
#scp -P $KINSTA_PORT -r ./wp-content/plugins $KINSTA_USER@$KINSTA_IP:./$DESTINATION/wp-content/

# NOTE: Uncomment this line to deploy must-use plugins
#scp -P $KINSTA_PORT -r ./wp-content/mu-plugins $KINSTA_USER@$KINSTA_IP:./$DESTINATION/wp-content/
