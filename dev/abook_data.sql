USE `abook`;

INSERT INTO `towns` (`id`, `name`) VALUES
(1, 'Oberdorf'),
(2, 'Tallinn'),
(3, 'London'),
(4, 'New York'),
(5, 'Tokyo'),
(6, 'Moscow');

INSERT INTO `contacts` (`id`, `fname`, `lname`, `street`, `zip`, `town_id`) VALUES
(1, 'John', 'Doe', '45. Street 11 apt. 4', '11200', 4),
(2, 'Иван', 'Денисович', 'Движенцев 17', '347658', 6),
(3, 'Ülle', 'Jõekäär', 'Šmidti 11', '20123', 2),
(4, 'Eric', 'Schwartz', 'Alpenstrasse 1', '4500', 1),
(5, 'Luis', 'Rodriguez', 'Manhattan', '11000', 4),
(6, 'Todd', 'Hamilton', 'Fulham Road', 'SW6 1EX', 3),
(7, 'Mito', 'Mitsuko', 'Hatagaya', '1-32-3', 5),
(8, 'Паша', 'Павличенко', 'Новоарбатское 6', '584248', 6),
(9, 'Ryan', 'Graham', 'Chelsea', 'SW1 9QJ', 3),
(10, 'Abby', 'Crary', '350 Fifth Avenue', '10118-', 4),
(11, 'Peeter', 'Tamm', 'Võidu 12', '11240', 2),
(12, 'Kobo', 'Abe', 'Mori Tower', '106-0032', 5),
(13, 'Leo', 'Klammer', 'Seeburgstrasse 53', '4515', 1),
(14, 'Villu', 'Veski', 'Vabaduse 1', '12300', 2);
