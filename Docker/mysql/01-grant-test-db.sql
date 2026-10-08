-- Lets the app user create and drop the test databases (politiconnect_test, politiconnect_test<TOKEN>).
-- Only runs on a fresh data volume; for an existing one, run the same GRANT as root.
GRANT ALL PRIVILEGES ON `politiconnect%`.* TO 'user'@'%';
FLUSH PRIVILEGES;
