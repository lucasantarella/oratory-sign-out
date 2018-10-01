# School Sign-Out
### Luca Santarella - 2018/10/01

## Functionality Overview

 * This software was created to manage student traffic within a school's buildings. Faculty are able to view a students scheduled and actual location instantaneously. Student movements are logged for auditing purposes and can be exported at any time. 

* Authentication is achieved through Google SSO. This ensures a students identity is accurate and no one else is signing them out of a room. By the same token, this ensures no students can impersonate a teacher and tamper with student movement logging.

* Both student and faculty views are instantaneously updated at the end of each period and when any student movement occurs, without refreshing the page. This makes monitoring activity in an always-open browser window or with a stationary installation (tablet/screen mounted to classroom wall) possible.

* For enhanced accountability, teachers can be alerted when a student signs out to their current room. The teacher with an incoming student must then "sign in" the student by acknowleding they have been admitted to the room. The teacher from whose room the student is originating is also alerted when a student is "signed in" to their new room.

* **Work is currently underway for student attendance & firedrill modules.**

## Feature Screenshots

### Student View - Not Signed Out
![Student View](https://media.githubusercontent.com/media/lucasantarella/oratory-sign-out/develop/imgs/screenshots/studentnewroom.png)

### Teacher View - No Students Signed Out
![Sign Out Room Selection](https://media.githubusercontent.com/media/lucasantarella/oratory-sign-out/develop/imgs/screenshots/teacherroom.png)

### Student View - Sign Out Room Selection
![Sign Out Room Selection](https://media.githubusercontent.com/media/lucasantarella/oratory-sign-out/develop/imgs/screenshots/studentsignout.png)

### Teacher View - Student Signed Out
![Sign Out Room Selection](https://media.githubusercontent.com/media/lucasantarella/oratory-sign-out/develop/imgs/screenshots/teachersignedout.png)

### Teacher View - Incoming Student
![Sign Out Room Selection](https://media.githubusercontent.com/media/lucasantarella/oratory-sign-out/develop/imgs/screenshots/teacherincoming.png)

### Teacher View - Student "Signed In"
![Sign Out Room Selection](https://media.githubusercontent.com/media/lucasantarella/oratory-sign-out/develop/imgs/screenshots/teacheraccepted.png)