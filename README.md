# Slide Piattaforma

This is a simple, configurable web application for creating and displaying presentations on different topics. Each presentation is accessed via a unique "slug" in the URL.

## Installation

### Prerequisites

*   PHP 8.3 or higher
*   MariaDB (or MySQL)
*   A web server (like Apache or Nginx), or you can use PHP's built-in server for development.

### Steps

1.  **Clone the repository:**
    ```bash
    git clone <repository-url>
    ```

2.  **Install PHP extensions:**
    ```bash
    sudo apt-get update
    sudo apt-get install -y php php-mysql
    ```

3.  **Install and configure MariaDB:**
    ```bash
    sudo apt-get install -y mariadb-server
    sudo service mariadb start
    ```

4.  **Set up the database:**
    - Log in to MariaDB as the root user:
      ```bash
      sudo mysql
      ```
    - Run the following SQL commands to create the database, user, and table:
      ```sql
      CREATE DATABASE slide;
      CREATE USER 'slide'@'localhost' IDENTIFIED BY 'xxxxxx'; -- Replace 'xxxxxx' with a strong password
      GRANT ALL PRIVILEGES ON slide.* TO 'slide'@'localhost';
      FLUSH PRIVILEGES;
      USE slide;
      CREATE TABLE presentations (
          slug VARCHAR(255) PRIMARY KEY,
          content TEXT
      );
      EXIT;
      ```

5.  **Update the database configuration:**
    - Open `api/config.php` and update the `pass` value with the password you set in the previous step.

## Usage

### Running the Application

For development, you can use PHP's built-in web server:

```bash
php -S 127.0.0.1:8080
```

Then, open your browser and navigate to `http://127.0.0.1:8080`.

### Creating a New Page

To create a new presentation page (e.g., for "italia"):

1.  **Change the URL:**
    - Go to `http://127.0.0.1:8080/index.php?slug=italia`.

2.  **Open the Editor:**
    - Click the "⚙️ Impostazioni Totali" button in the footer.

3.  **Edit the Content:**
    - The editor will load with the default content. Modify the text, images, and data to match your new topic.

4.  **Save:**
    - Click the "💾 SALVA SUL SERVER" button.

The new page is now saved and will be loaded anytime you visit that URL.
