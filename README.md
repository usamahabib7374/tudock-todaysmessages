# tudock-todaysmessages
## Instructions
1 - Clone the module code it under app/code/Tudock/TodaysMessage directory<br/>
2 - run magento setup commands<br/>
3 - under Store Condifurations Tudock-> configuration create messages for serveral categories<br/>
4 - In Index Management there is a indexer listed as *Tudock TodaysMessage Indexer*<br/>
5 - command to run indexer *php bin/magento indexer:reindex tudock_todaysmessage_indexer*<br/>
6 - If Indexer is under Update on Save mode it will save on product and category save. <br/>
7- Update on Schedule will reindex the whole catalog by cron and also the command do the same<br/>
