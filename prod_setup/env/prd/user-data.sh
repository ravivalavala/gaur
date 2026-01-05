#!/bin/bash
# Production EC2 UserData script
# Load environment variables
export $(grep -v '^#' /home/ubuntu/env/prd/.env | xargs)

# Run shared WordPress setup script
bash /home/ubuntu/scripts/setup-wordpress.sh
