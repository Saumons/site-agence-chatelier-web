<?php

require __DIR__ . '/../config.php';

$pageTitle = "Accueil";

require __DIR__ . '/../templates/header.php';

?>

    <img class="page-background page-background--accueil" src="./assets/images/svg/fonds/accueil.svg" alt="">

    <main class="main main--home">
        <header class="main__header">
            <h1 class="main__title">
                Chat'elier Web
            </h1>
            <h2 class="main__subtitle">
                Des projets agiles, des résultats au poil
            </h2>
        </header>

            <section class="main__section">
                <h2 class="main__subtitle">
                    Qui sommes-nous ?
                </h2>
                <p class="main__paragraph">
                    Placeholder Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.<br><br>
                    Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.<br><br>
                    Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.
                </p>
            </section>

            <section class="main__section main__section--team">
                <h2 class="main__subtitle">
                    Notre Équipe
                </h2>
                <article class="main__article card-member">
                    <img class="card-member__image" src="" alt="Falafel Picard">
                    <h3 class="card-member__name">Falafel Picard</h3>
                    <p class="card-member__description">Ne fait rien</p>
                </article>

                <article class="main__article card-member">
                    <img class="card-member__image" src="" alt="Clovis Jouanneau">
                    <h3 class="card-member__name">Clovis Jouanneau</h3>
                    <p class="card-member__description">Back-end</p>
                </article>

                <article class="main__article card-member">
                    <img class="card-member__image" src="" alt="Eydan Gannot">
                    <h3 class="card-member__name">Eydan Gannot</h3>
                    <p class="card-member__description">Designer par défaut/Front-end</p>
                </article>

                <article class="main__article card-member">
                    <img class="card-member__image" src="" alt="Dorian Adam">
                    <h3 class="card-member__name">Dorain Adam</h3>
                    <p class="card-member__description">Référent Harcèlement</p>
                </article>
            </section>

            <section class="main__section">
                <h2 class="main__subtitle">
                    Nos Engagements
                </h2>
                <p class="main__paragraph">
                    Placeholder Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.<br><br>
                    Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.<br><br>
                    Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.
                </p>
                <a href="nosEngagements.php" class="main__contact">Placeholder</a>
            </section>

    </main>


<?php require __DIR__ . '/../templates/footer.php'; ?>