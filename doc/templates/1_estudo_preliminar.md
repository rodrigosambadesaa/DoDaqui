# Estudo preliminar – Anteproxecto

## Descrición do proxecto

**Tenda DoDaquí** é unha plataforma web de comercio electrónico orientada á venda de produtos econaturais galegos procedentes exclusivamente de produtores locais. O proxecto nace coa finalidade de fomentar a economía de proximidade, promover o consumo responsable e ofrecer ao consumidor final unha alternativa transparente, sostible e de calidade fronte ás grandes plataformas de venda xeralista.

A aplicación permitirá a comercialización de alimentos e produtos naturais sen aditivos artificiais, cunha trazabilidade clara da súa orixe e cun enfoque centrado no respecto polo medio ambiente e polos pequenos produtores galegos.

---

### Xustificación do proxecto

Na actualidade, o mercado dixital está dominado por grandes plataformas que priorizan a produción masiva, a importación a gran escala e a redución de custos por riba da calidade, da sustentabilidade e do impacto económico local.

Este proxecto xustifícase pola necesidade de:
- Apoiar aos pequenos produtores galegos, que a miúdo carecen de canles dixitais propias.
- Facilitar ao consumidor o acceso a produtos naturais, de proximidade e sen intermediarios abusivos.
- Promover valores como a sustentabilidade, o consumo responsable e a economía circular.
- Crear unha solución tecnolóxica moderna que integre comercio electrónico, xestión de usuarios, pedidos e produtos, cumprindo os estándares actuais de desenvolvemento web.

Desde o punto de vista formativo, o proxecto permite aplicar coñecementos reais de desenvolvemento web tanto no backend como no frontend, así como en bases de datos, seguridade, despregue e contedorización.

---

### Funcionalidades do proxecto

A aplicación contará, como mínimo, coas seguintes funcionalidades:

**Funcionalidades para clientes:**
- Rexistro e autenticación de usuarios.
- Navegación polo catálogo de produtos.
- Filtrado de produtos por categoría, prezo ou produtor.
- Visualización do detalle dun produto (descrición, prezo, orixe, produtor).
- Xestión do carriño da compra.
- Realización de pedidos.
- Consulta do historial de pedidos.
- Xestión do perfil de usuario.

**Funcionalidades para administradores:**
- Xestión de produtos (alta, modificación e baixa).
- Xestión de categorías.
- Xestión de produtores.
- Consulta e xestión de pedidos.
- Control de usuarios rexistrados.

**Funcionalidades técnicas:**
- API REST para a comunicación entre frontend e backend.
- Autenticación segura.
- Persistencia de datos nunha base de datos relacional.
- Contedorización completa mediante Docker.
- Despregue en contorno de produción reproducible.

---

### Estudo de necesidades. Proposta de valor respecto ao que hai no mercado

Actualmente existen tendas online de produtos ecolóxicos, pero moitas delas:
- Non están especializadas en produto galego.
- Non garanten a procedencia local real.
- Actúan como simples intermediarios sen retorno directo ao produtor.

A proposta de valor de **Tenda DoDaquí** baséase en:
- Especialización exclusiva en produtos galegos.
- Transparencia total sobre a orixe e o produtor.
- Apoio directo á economía local.
- Experiencia de usuario sinxela, clara e moderna.
- Enfoque sostible e responsable, tanto a nivel comercial como tecnolóxico.

---

### Persoas destinatarias

O proxecto está orientado principalmente a:

- Consumidores concienciados coa sustentabilidade e o consumo responsable.
- Persoas interesadas en produtos naturais e de proximidade.
- Consumidores que desexan apoiar á economía local galega.
- Pequenos produtores que buscan unha canle dixital para comercializar os seus produtos.

---

### Promoción

A promoción da plataforma poderá realizarse a través de:
- Redes sociais (Instagram, Facebook, etc.).
- Colaboracións con produtores locais.
- Campañas de concienciación sobre consumo responsable.
- Presenza en feiras ou eventos relacionados coa sustentabilidade e o produto local.
- Estratexias de posicionamento web (SEO) centradas en produto galego e ecolóxico.

---

### Modelo de negocio

O modelo de negocio proposto baséase en:
- Comisión por venda realizada a través da plataforma.
- Posible cota premium para produtores que desexen maior visibilidade.
- Promoción destacada de produtos ou produtores.
- Evolución futura cara a subscricións ou packs de produtos.

Este modelo permite a sustentabilidade económica do proxecto sen prexudicar aos produtores nin encarecer excesivamente o produto final.

---

## Requirimentos

Descrición dos medios materiais e das tecnoloxías necesarias que se usarán para desenvolver o proxecto, incluíndo linguaxes de programación frontend e backend, técnicas, librerías, base de datos, servizos, servidores e API.

**Infraestrutura:**
- Dominio público: `rodrigosambade.gal`.
- Servidor web dedicado para publicar a aplicación e servir os recursos frontend.
- Servidor de backend con soporte para `PHP` e execución do servizo de API.
- Servidor de base de datos relacional SQL para a persistencia de usuarios, produtos, pedidos e categorías.
- Almacenamento persistente para datos da aplicación e recursos estáticos (imaxes e contido multimedia).
- Memoria RAM e capacidade de procesamento suficientes para soportar a carga prevista e o crecemento do proxecto.
- Comunicación entre frontend e backend mediante API HTTP/REST.

**Backend (tecnoloxías usadas):**
- `PHP` como linguaxe principal do backend.
- Desenvolvemento sen frameworks, con arquitectura propia baseada en rutas, controladores e acceso a datos.
- Exposición de API REST para autenticación, xestión de catálogo, carriño e pedidos.
- Integración cunha base de datos relacional para a capa de persistencia.

**Frontend (tecnoloxías usadas):**
- `HTML5`, `CSS3` e `JavaScript` como tecnoloxías principais para o desenvolvemento do frontend.
- Desenvolvemento sen frameworks frontend.
- Interface construída con tecnoloxías web estándar (HTML, CSS e JavaScript).
- Consumo da API REST do backend para operacións de autenticación, catálogo e xestión de pedidos.
