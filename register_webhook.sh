#!/bin/bash

curl -X POST https://api.telegram.org/bot{$1}/setWebhook -d "url={$2}"
