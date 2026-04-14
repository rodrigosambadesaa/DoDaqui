# Análise: Requirimentos do sistema

## Descrición xeral

Este documento recolle os requirimentos do sistema para o proxecto **Tenda DoDaquí**, que basicamente é unha tenda online centrada en produtos econaturais galegos, todos eles de produtores locais (a idea é darlle valor ao produto de aquí, non tanto competir coas grandes plataformas).

Nesta fase o que se busca é deixar claro que vai facer a aplicación e como van interactuar os distintos tipos de usuarios co sistema. Non é tanto “como se fai”, senón máis ben “que ten que facer”. Isto logo servirá de base para o deseño e para definir os casos de uso máis adiante.

En principio, a aplicación estará publicada no dominio `rodrigosambade.gal`. A arquitectura será a típica cliente-servidor:  
- Backend en `PHP`
- Frontend con `HTML5`, `CSS3` e algo de `JavaScript`  
- Comunicación mediante API REST (para manter todo un pouco máis ordenado e separado)

---

## Requirimentos

### Requirimentos funcionais

1. **Rexistro de usuario**
Non é preciso ter iniciada sesión previamente  

2. **Inicio e peche de sesión**  
Non é preciso ter iniciada sesión previamente para o inicio de sesión, si para o peche

3. **Consulta do catálogo de produtos**  
Non é preciso ter iniciada sesión previamente  

4. **Filtrado e busca de produtos**
Non é preciso ter iniciada sesión previamente  

5. **Visualización do detalle de produto**  
Non é preciso ter iniciada sesión previamente  

6. **Xestión do carriño da compra**
É preciso ter iniciada sesión previamente  

7. **Realización de pedido**
É preciso ter iniciada sesión previamente  

8. **Consulta do historial de pedidos**
É preciso ter iniciada sesión previamente  

9. **Xestión do perfil de usuario**
É preciso ter iniciada sesión previamente  

10. **Opinións dos clientes**
Para velas non é preciso iniciar sesión, para deixalas si

---

### Requirimentos non funcionais

1. **Seguridade da información**

2. **Dispoñibilidade e rendemento**

3. **Compatibilidade e dispositivos**

4. **Accesibilidade**

5. **Escalabilidade e mantibilidade**

6. **Fiabilidade dos datos**

---

## Tipos de usuarios

1. **Usuario anónimo**

2. **Usuario rexistrado**