param (
    [Parameter(Mandatory = $true)]
    [string]$EnvName
)

Write-Host "====================================="
Write-Host " WordPress Deployment Script (AWS)"
Write-Host " Environment: $EnvName"
Write-Host "====================================="

# -------------------------------
# Variables
# -------------------------------
$AWS_REGION = "us-east-1"
$STACK_NAME = "wordpress-$EnvName"
$TEMPLATE_FILE = "cloudformation/stack.yml"
$PARAM_FILE = "cloudformation/parameters-$EnvName.json"

# -------------------------------
# Validation
# -------------------------------
if (!(Test-Path $TEMPLATE_FILE)) {
    Write-Error "CloudFormation template not found: $TEMPLATE_FILE"
    exit 1
}

if (!(Test-Path $PARAM_FILE)) {
    Write-Error "Parameter file not found: $PARAM_FILE"
    exit 1
}

# -------------------------------
# AWS Identity Check
# -------------------------------
Write-Host ""
Write-Host "Checking AWS credentials..."

aws sts get-caller-identity --region $AWS_REGION | Out-Null

if ($LASTEXITCODE -ne 0) {
    Write-Error "AWS credentials not configured. Run: aws configure"
    exit 1
}

# -------------------------------
# Create Stack
# -------------------------------
Write-Host ""
Write-Host "Creating CloudFormation stack: $STACK_NAME"

aws cloudformation create-stack `
    --stack-name $STACK_NAME `
    --template-body file://$TEMPLATE_FILE `
    --parameters file://$PARAM_FILE `
    --capabilities CAPABILITY_NAMED_IAM `
    --region $AWS_REGION

if ($LASTEXITCODE -ne 0) {
    Write-Error "Failed to create stack."
    exit 1
}

# -------------------------------
# Wait for Stack Completion
# -------------------------------
Write-Host ""
Write-Host "Waiting for stack to complete. This may take 10-15 minutes..."

aws cloudformation wait stack-create-complete `
    --stack-name $STACK_NAME `
    --region $AWS_REGION

if ($LASTEXITCODE -ne 0) {
    Write-Error "Stack creation failed. Check CloudFormation events."
    exit 1
}

# -------------------------------
# Output Stack Info
# -------------------------------
Write-Host ""
Write-Host "Stack created successfully!"
Write-Host ""
Write-Host "Fetching stack outputs..."

aws cloudformation describe-stacks `
    --stack-name $STACK_NAME `
    --region $AWS_REGION `
    --query "Stacks[0].Outputs" `
    --output table

Write-Host ""
Write-Host "====================================="
Write-Host " Deployment Complete"
Write-Host "====================================="
Write-Host "Next steps:"
Write-Host "1. Copy the WordPress URL from above"
Write-Host "2. Open it in your browser"
Write-Host "3. Complete WordPress installation"
