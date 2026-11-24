<?php

require __DIR__ . '/../config.php';

$pageTitle = "Nos Services";

require __DIR__ . '/../templates/header.php';

?>
<img class="page-background page-background--engagements" src="./assets/images/svg/fonds/nosServices.svg" alt="">


<main class="main">
    <header class="main__header">
        <h1 class="main__title">
            Nos Services
        </h1>
        <p class="main__intro">Vous désirez un site vitrine, un site e-commerce ou une application web, mais vous
            recherchez bien plus qu'un
            simple prestataire ? Chez Chat'elier Web, nous avançons avec l'agilité d'un chat, en
            construisant des solutions durables dans un atelier où confiance, transparence et écoresponsabilité sont au
            cœur de
            chaque projet.</p>
    </header>

    <section class="main__section">
        <h2 class="main__subtitle"> Nos différentes formules : </h2>
        <section class="main__packages">

            <!-- Carte : Site vitrine -->
            <article class="packages__card-service">
                <h3 class="card-service__title">Site vitrine</h3>
                <p class="card-service__description">
                    Idéal pour présenter votre activité en ligne : une vitrine claire et professionnelle
                    pour les indépendants, artisans et TPE qui veulent être visibles sur le web.
                </p>
                <ul class="card-service__features">
                    <h4 class="card-service__subtitle">Cette offre contient :</h4>
                    <li>3 pages incluses</li>
                    <li>Design responsive (mobile / tablette / desktop)</li>
                    <li>Formulaire de contact</li>
                    <li>Hébergement / nom de domaine (si inclus)</li>
                    <li>Référencement de base (SEO)</li>
                </ul>
                <p class="card-service__price">À partir de <strong>900 €</strong></p>
                <a href="devis.php?tab=vitrine" class="card-service__cta">Faire un devis</a>
            </article>

            <!-- Carte : Site E-commerce -->
            <article class="packages__card-service">
                <h3 class="card-service__title">Site E-commerce</h3>
                <p class="card-service__description">
                    Pour vendre vos produits en ligne : une boutique complète, sécurisée et prête
                    à encaisser vos premières commandes.
                </p>
                <ul class="card-service__features">
                    <h4 class="card-service__subtitle">Cette offre contient :</h4>
                    <li>Catalogue produits</li>
                    <li>Panier et tunnel de paiement sécurisé</li>
                    <li>Gestion des stocks / commandes</li>
                    <li>Design responsive</li>
                    <li>Référencement de base (SEO)</li>
                </ul>
                <p class="card-service__price">À partir de <strong>6 900 €</strong></p>
                <a href="devis.php?tab=commerce" class="card-service__cta">Faire un devis</a>
            </article>

            <!-- Carte : WebApp -->
            <article class="packages__card-service">
                <h3 class="card-service__title">WebApp</h3>
                <p class="card-service__description">
                    Pour les projets qui ont besoin de fonctionnalités sur-mesure : espace client,
                    outil interne, plateforme métier ou application web interactive.
                </p>
                <ul class="card-service__features">
                    <h4 class="card-service__subtitle">Cette offre contient :</h4>
                    <li>Cahier des charges / étude des besoins</li>
                    <li>Interface sur-mesure</li>
                    <li>Base de données</li>
                    <li>Authentification / espace utilisateur</li>
                    <li>Maintenance et évolutions</li>
                </ul>
                <p class="card-service__price">À partir de <strong>3 200 €</strong></p>
                <a href="devis.php?tab=webApp" class="card-service__cta">Faire un devis</a>
            </article>
        </section>
    </section>


    <!-- Formules de maintenance -->
    <section class="main__section main__maintenance">
        <h2 class="main__subtitle">Formules de maintenance : </h2>
        <div class="maintenance__table-wrapper">
            <table class="maintenance__table">
                <thead>
                <tr>
                    <th>Formule</th>
                    <th>Site vitrine</th>
                    <th>E-commerce</th>
                    <th>Web App</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Maintenance classique</td>
                    <td>À partir de <strong>33 €</strong></td>
                    <td>À partir de <strong>67 €</strong></td>
                    <td>À partir de <strong>127 €</strong></td>
                </tr>
                <tr>
                    <td>Maintenance + hébergement</td>
                    <td>À partir de <strong>49 €</strong></td>
                    <td>À partir de <strong>99 €</strong></td>
                    <td>À partir de <strong>179 €</strong></td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
