create database usuario;
use usuario;

create table carreras_academicas(
id int auto_increment primary key,
nombre varchar(25) not null,	
numCreditos int not null,
numAsignaturas int not null,
numSemestres int not null,
nivelFormacion varchar(25) not null,
titulo varchar(25) not null,
valorsemestre double not null,
universidad varchar(25) not null,
esAcreditada boolean not null,
perfiles varchar(25) not null,
areaConocmiento varchar(25) not null
);

select  * from  carreras_academicas;
create table usuarios(
	id int auto_increment primary key,
    nombre varchar(25) not null,
    email varchar(25) not null,
    contraseña varchar(25) not null,
    id_carrera int,
    FOREIGN KEY (id_carrera) REFERENCES carreras_academicas(id)
) 
select  * from  usuarios;

insert into carreras_academicas (nombre, numCreditos , numAsignaturas, numSemestres, nivelFormacion, titulo, valorsemestre, universidad, esAcreditada, perfiles, areaConocmiento) values ('Ingenierias Sistemas', 61, 35, 10, 'Profesional','Ingeniero de Sistemas', 3500000, 'Universidad de la Vida', 0, 'Egresado', 'Ingeniería y Tecnología');
insert into usuarios (nombre, email, contraseña, id_carrera) values ('Alejandro', 'alejandrogay@gmail.com', 'elvideodebeele', 1);

select u.nombre, u.email, u.contraseña, ca.nombre  from usuarios u 
inner join carreras_academicas ca on ca.id = u.id_carrera;

select * from usuarios;

insert into carreras_academicas (nombre, numCreditos , numAsignaturas, numSemestres, nivelFormacion, titulo, valorsemestre, universidad, esAcreditada, perfiles, areaConocmiento) values ('Administración de Empresas', 55, 32, 9, 'Profesional','Administrador de Empresas', 2800000, 'Universidad del Futuro', 1, 'Gestor Empresarial', 'Ciencias Económicas');

insert into carreras_academicas (nombre, numCreditos , numAsignaturas, numSemestres, nivelFormacion, titulo, valorsemestre, universidad, esAcreditada, perfiles, areaConocmiento) 
values ('Psicología', 60, 34, 10, 'Profesional','Psicólogo', 3000000, 'Instituto Superior del Saber', 1, 'Investigador y Terapeuta', 'Ciencias Sociales y Humanas');

insert into carreras_academicas (nombre, numCreditos , numAsignaturas, numSemestres, nivelFormacion, titulo, valorsemestre, universidad, esAcreditada, perfiles, areaConocmiento) 
values ('Contaduría Pública', 50, 30, 8, 'Profesional','Contador Público', 2500000, 'Universidad Financiera', 0, 'Asesor Contable', 'Ciencias Económicas');

insert into carreras_academicas (nombre, numCreditos , numAsignaturas, numSemestres, nivelFormacion, titulo, valorsemestre, universidad, esAcreditada, perfiles, areaConocmiento) 
values ('Enfermería', 62, 36, 10, 'Profesional','Enfermero', 3200000, 'Universidad de la Salud', 1, 'Profesional Clínico', 'Ciencias de la Salud');

insert into carreras_academicas (nombre, numCreditos , numAsignaturas, numSemestres, nivelFormacion, titulo, valorsemestre, universidad, esAcreditada, perfiles, areaConocmiento) 
values ('Arquitectura', 65, 38, 10, 'Profesional','Arquitecto', 4000000, 'Universidad Creativa', 1, 'Diseñador de Proyectos', 'Arquitectura y Urbanismo');
