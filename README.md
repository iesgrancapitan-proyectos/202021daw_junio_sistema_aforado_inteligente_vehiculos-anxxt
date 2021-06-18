# Sistema de Aforamiento Inteligente de Vehículos

- [Sistema de Aforamiento Inteligente de Vehículos](#sistema-de-aforamiento-inteligente-de-vehículos)
  - [Descripción del proyecto](#descripción-del-proyecto)
  - [Información sobre despliegue](#información-sobre-despliegue)
  - [Información sobre cómo usarlo](#información-sobre-cómo-usarlo)
  - [Autor](#autor)

## Descripción del proyecto

Se trata de un sistema dotado con dispositivos hardware de captación de imagen y software de procesamiento, capaz de
leer las matrículas de los vehículos que transitan por una determinada vía, realizando el registro de la información en
una base de datos consultable mediante una interfaz web.

El sistema permitirá conocer el aforo de vehículos de forma identificada. Ello permitirá conocer el uso permanente de la
infraestructura, así como proporcionar el registro discriminado de los usuarios de la vía, a las administraciones locales competentes.

Los elementos se instalarán en un bolardo urbano modelo Barcelona ‘92. Así mismo contará con los elementos de
protección, alimentación y comunicación necesarios para el correcto funcionamiento técnico adecuado a la normativa
vigente.

## Información sobre despliegue

[Información sobre despliegue - Wiki](https://github.com/iesgrancapitan-proyectos/202021daw_junio_sistema_aforado_inteligente_vehiculos-anxxt/wiki/Manual_Despliegue)

## Información sobre cómo usarlo

Ejecutaremos el archivo main.py en nuestra raspberry pi con cámara donde podremos pasar una matrícula y que esta sea reconocida.

Una vez sea reconocida se enviará al servidor y podremos ver los resultados en nuestra interfaz web.

## Autor

- Antonio García García ([@anxxt](https://www.github.com/anxxt))