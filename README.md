Database Configuration Instructions
Follow the steps below to configure the database for the project:

1.Download the SQL File

    .Download the taskmanagement.sql file provided with the project.
    
2.Create a New Database in phpMyAdmin

  .Open phpMyAdmin.
  .Create a new database. You can name the database anything you prefer.
  
3.Import the SQL File

  .Select the new database you just created.
  .Click on the Import tab in phpMyAdmin.
  .Upload the taskmanagement.sql file and execute the import process.

4.Configure Environment File

  .Open the .env file in the project root directory.
  .Update the database credentials to match your new database configuration. Example:

      DB_CONNECTION=mysql
      DB_HOST=localhost
      DB_PORT=3306
      DB_DATABASE=task_management
      DB_USERNAME=root
      DB_PASSWORD=
      
  .Replace DB_DATABASE with the name of your database.
  .Update DB_USERNAME and DB_PASSWORD according to your phpMyAdmin credentials.

