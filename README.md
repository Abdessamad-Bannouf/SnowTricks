# SnowTricks

# Description du besoin :

Vous êtes chargé de développer le site répondant aux besoins de Jimmy. Vous devez ainsi implémenter les fonctionnalités suivantes :   


    un annuaire des figures de snowboard. Vous pouvez vous inspirer de la liste des figures sur Wikipédia. Contentez-vous d'intégrer 10 figures, le reste sera saisi par les internautes ;  

    la gestion des figures (création, modification, consultation) ;  

    un espace de discussion commun à toutes les figures.  
      


Pour implémenter ces fonctionnalités, vous devez créer les pages suivantes :

    ● la page d’accueil où figurera la liste des figures ;  

    ● la page de création d'une nouvelle figure ;  

    ● la page de modification d'une figure ;  

    ● la page de présentation d’une figure (contenant l’espace de discussion commun autour d’une figure).  

  

Page d’accueil - Liste des figures de snowboard
La page est accessible par tous les utilisateurs. On y verra la liste des noms de figure. L’utilisateur a la possibilité de cliquer sur le nom d’une figure pour accéder à la page de détail de cette figure.

Si l’utilisateur est connecté, il pourra cliquer sur :  
    ● une petit icône en forme de stylo situé juste à côté du nom qui redirigera l’utilisateur vers un formulaire de modification de figure ;  

    ● une corbeille située juste à côté du nom pour supprimer la figure.  

    ● Page de création de figure de snowboard
      
Le formulaire comportera les champs suivants :  
    ● nom ;  

    ● description ;  

    ● groupe de la figure ;  

    ● une ou plusieurs illustration(s) ;  

    ● une ou plusieures vidéo(s).

Le formulaire n’est accessible que si l’utilisateur est authentifié.  
  

Lorsque l’utilisateur soumet le formulaire, il faut que :  

    ● cette figure n’existe pas déjà en base de données (contrainte d’unicité sur le nom) ;  

    ● il soit redirigé sur la page du formulaire en cas d'erreur, en précisant le(s) type(s) d'erreurs ;  

    ● il soit redirigé sur la page listant des figures avec un message flash donnant une indication concernant le bon déroulement de l'enregistrement en base de données en cas de succès.  


  Pour les vidéos, l’utilisateur pourra coller une balise embed provenant de la plateforme de son choix (Youtube, Dailymotion…).
  Page de modification de figure de snowboard.

Les besoins sont les mêmes que pour la création. La seule différence est qu’il faut que les champs soient pré remplis au moment où l’utilisateur arrive sur cette page.
Page de présentation d’une figure.
Les informations suivantes doivent figurer sur la page :  

    ● nom de la figure ;  

    ● sa description ;  

    ● le groupe de la figure ;  

    ● la ou les photos rattachées à la figure ;  

    ● la ou les vidéos rattachées à la figure ;  

    ● l’espace de discussion (plus de détail à la section suivante).  
  

Les utilisateurs qui ne sont pas authentifiés peuvent consulter les discussions de toutes les figures. En revanche, ils ne peuvent pas poster de message.  
  

Pour chaque message, il sera affiché les informations suivantes :  

    ● le nom complet de l’auteur du message ;  

    ● la photo de l’auteur du message ;  

    ● la date de création du message ;  

    ● le contenu du message.  
      

Dans cet espace de discussion, on peut voir la liste des messages postés par les membres, du plus récent au plus ancien.  
Ces messages doivent être paginés (10 par pages).  
  
  
Si l’utilisateur est authentifié, il peut voir un formulaire au dessus de la liste avec un champs “message” qui est obligatoire. L’utilisateur peut poster autant de message qu’il le souhaite.  
  

  
# Installation du projet :  
  
    ● Cloner le projet : git clone https://github.com/Abdessamad-Bannouf/SnowTricks.git  
    
    ● Installer le gestionnaire de dépendance : composer  

    ● Lancer la commande : composer install  

    ● Lancer la commande : php bin/console doctrine:database:create.  

    ● Lancer la commande : php bin/console doctrine:migrations:migrate  

    ● Lancer la commande : php bin/console doctrine:fixtures:load  

    ● Aller sur l'url : https://localhost:8000/home
