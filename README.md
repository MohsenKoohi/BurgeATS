#Burge Affairs Tracking System

Burge ATS, is a simple and open source CRM, which focus on task execution. It has been implemented using BurgeCMF. 

Features:
* Multi language admin and custoemr environments, very useful for multi-language companies.
* Customer manager module, which allows add and edit customer properties, tracking customer logs, and executing tasks.
* Customer manager module includes a powerful logger, which creates a file for each event of customer and stores it in JSON format to the related customer folder. 
* **Task Manager** module, which declares each task. Each task has a specific class which is called by the system scheduler to specify users for which task should be executed.
* **Task Execution** module, which executes a task for a user and creates its log. some executions require manger note.
* For each task, it is possible to set users, who execute that task, and also managers who can consider and note about that task.
* Each customer has some events(for example requires urgent call, or has sent an email), which indicates an action should be done for that customer. These events can be defined in the customer manager and the a task can check if a customer's flag has been raised, and ask a user to consider the events.
* A comprehensive **Ticketing System**, defined as *Message Manager* module, which allows messaging between different parts of organizaitons including departments, customers, and users. It also allow adding participants from another departments to a message, in-organization commenting on messagges (private to the customers), and specifying access level of each user to messages.

