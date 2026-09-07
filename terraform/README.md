# AWS WordPress Infrastructure via Terraform

This project automates the deployment of a production-ready WordPress site on AWS using Terraform. It follows a "Infrastructure as Code" (IaC) approach, deploying a custom VPC, an RDS MySQL database, and an Ubuntu EC2 instance running Nginx and PHP 8.2.



## 🏗️ Architecture Components
* **VPC:** Custom 10.0.0.0/16 network with Public Subnets.
* **Database:** AWS RDS (MySQL 8.0) instance for persistent data.
* **Web Server:** EC2 (t3.micro) running Ubuntu 22.04.
* **Stack:** Nginx, PHP 8.2-FPM, and WordPress Latest.
* **Security:** Isolated Security Groups for Web (Port 80/22) and DB (Port 3306).

## 🚀 Getting Started

### 1. Prerequisites
* [Terraform](https://www.terraform.io/downloads.html) installed.
* AWS CLI configured with your credentials (`aws configure`).
* An AWS Key Pair (.pem file) named `desti` downloaded to this folder.

### 2. File Structure
* `main.tf`: The primary infrastructure configuration.
* `setup.sh`: The "User Data" bash script that installs the software stack on boot.
* `terraform.tfstate`: (Generated) The current state of your AWS resources.

### 3. Deployment Commands
To deploy the infrastructure, run:

```powershell
# Initialize Terraform providers
terraform init

# Plan and Deploy
terraform apply -var="db_password=YourSecurePasswordHERE"


Cleanup
To avoid ongoing AWS costs, destroy the infrastructure when finished:

PowerShell

terraform destroy -var="db_password=YourSecurePasswordHERE"

---

### How to use this file
1. Create a new file in your `C:\Users\valav\workspace\getgaur_wp\gaur\terraform>` folder.
2. Name it **`README.md`**.
3. Paste the content above and save it.



### Your Next Step
Now that your documentation and infrastructure are solid, **would you like me to s