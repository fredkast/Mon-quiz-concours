<?php
// *******************************************************************************************************************************************************************************************************************************************************************
// ******************************************************************************************************* FRONT OFFICE *******************************************************************************
// *******************************************************************************************************************************************************************************************************************************************************************
/*
 * Insertion du jeu coucours dans un emplacement designé par le client
 */
// Récupération du Hook action : 'shortcode' , exécution de la fonction 'mqc_shortcode'
 add_shortcode('mon_quiz_concours', 'mqc_shortcode_fctn' );

function mqc_shortcode_fctn(){
    echo "<div class='wrap'>";

    // Le joueur a repondu correctement a toutes les questions :
    if(isset($_POST['send_data_correct'])){
        global $wpdb;
        $table = $wpdb->prefix."mqc_plugin_user";
        $table2 = $wpdb->prefix."mqc_plugin_game_settings";
        $email_to_check = $_POST['user_email'];

        // On verifie si l'Email de l'utilisateur a déja été inserer dans la BDD 
        $query = $wpdb ->get_row("SELECT * FROM $table WHERE user_email = '$email_to_check' ");
        $user_answer_success = $query->user_answer_success; 

        $qry = $wpdb ->prepare("SELECT * FROM $table2 ORDER BY id DESC LIMIT 1 ");
        $results = $wpdb->get_results($qry);
        foreach($results as $result){

            $end_date = $result->end_date;
            setlocale (LC_TIME, 'fr_FR','fra');

            // Email retrouvé : 2 options possibles :
            if(null !== $query){
               
                // Le joueur a déja joué, SI il etait en correct alors il n'est pas réinscrit
                if($user_answer_success == 'correct'){
                
                    echo "
                    <p id='mqc_heading_3_false' class='mqc_p'>Vous êtes déja inscrit au tirage au sort!</p>
                    <p class='mqc_p'>Inutile de rejouer!</p>
                    <form class='mqc_text_align_center' method='get'>
                    <input type='submit' name='' value='Revenir au jeu'>
                    ";

                // SINON SI il etait en false alors il est modifié en correct

                }elseif($user_answer_success == 'false'){
                    $wpdb->update( $table, array( 'user_answer_success' => 'correct'), array( 'user_email' => $email_to_check ) );
                    $user_firstname = htmlspecialchars($_POST['user_firstname']);
                    echo "
                    <script>alert(\"Bonnes réponses !\")</script>
                    <p id='mqc_heading_3_success' class='mqc_p'>Félicitation <b>".$user_firstname."</b> vous êtes inscrit au tirage au sort du concours! </p>
                    <p class='mqc_p'>Les gagnants seront contactés à l'issue du tirage au sort qui aura lieu le <b>".strftime("%A %d %B %G",strtotime($end_date))."</b>.</p>
                    <form class='mqc_text_align_center' method='get'>
                    <input type='submit' name='' value='Revenir au jeu'>
                    ";
                }
            // Email non retrouvé donc inscription en correct
            }elseif(null == $query){
                $datas = array(
                            'user_gender'=>$_POST['user_gender'],
                            'user_name'=>$_POST['user_name'],
                            'user_firstname'=>$_POST['user_firstname'],
                            'user_email'=>$_POST['user_email'],
                            'user_birthdate'=>$_POST['user_birthdate'],
                            'user_address'=>$_POST['user_address'],
                            'user_answer_success'=> 'correct',
                            );
                $wpdb->insert($table,$datas,$array);
                $user_firstname = htmlspecialchars($_POST['user_firstname']);
                echo "
                <script>alert(\"Bonnes réponses !\")</script>
                <p id='mqc_heading_3_success' class='mqc_p'>Félicitation <b>".$user_firstname."</b> vous êtes inscrit au tirage au sort du concours! </p>
                <p class='mqc_p'>Les gagnants seront contactés à l'issue du tirage au sort qui aura lieu le <b>".strftime("%A %d %B %G",strtotime($end_date))."</b>.</p>
                <form class='mqc_text_align_center' method='get'>
                <input type='submit' name='' value='Revenir au jeu'>
                ";
            }
        }
    // Le joueur a mal repondu a une ou toutes les questions :
    }elseif(isset($_POST['send_data_false'])){
            global $wpdb;
            $table = $wpdb->prefix."mqc_plugin_user";
            $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "mqc_plugin_game_settings ORDER BY id DESC LIMIT 1");
            $results = $wpdb->get_results($query);
            foreach ($results as $result){

                $end_date = $result->end_date;
                setlocale (LC_TIME, 'fr_FR','fra');

                $email_to_check = $_POST['user_email'];
                // On verifie si l'Email de l'utilisateur a déja été inserer dans la BDD
                $query = $wpdb ->get_row("SELECT * FROM $table WHERE user_email = '$email_to_check' ");

                // SI il est deja inscrit alors il n'est pas réinscrit
                if ( null !== $query) {
                    echo "
                    <script>alert(\"Mauvaises réponses !\")</script>
                    <p id='mqc_heading_3_replay' class='mqc_p'>Retentez votre chance</p>
                    <p class='mqc_p'>Vous y êtes presque!</p>
                    <form class='mqc_text_align_center' method='get'>
                    <input type='submit' name='' value='Revenir au jeu'>
                    ";

                //SINON SI il n'est pas déja inscrit alors il est inscrit en FALSE
                }elseif(null == $query){
                // L'email na pas été trouvé on laisse donc les données vont etre insérées.
                    $datas = array(
                                'user_gender'=>$_POST['user_gender'],
                                'user_name'=>$_POST['user_name'],
                                'user_firstname'=>$_POST['user_firstname'],
                                'user_email'=>$_POST['user_email'],
                                'user_birthdate'=>$_POST['user_birthdate'],
                                'user_address'=>$_POST['user_address'],
                                'user_answer_success'=> 'false',
                                );
                    $wpdb->insert($table,$datas,$array);
                    echo "
                        <script>alert(\"Mauvaises réponses !\")</script>
                        <p id='mqc_heading_3_replay' class='mqc_p'>Dommage!</p> 
                        <p class='mqc_p'>Vous n'êtes pas inscrit au tirage au sort! <br>
                        Vous pouvez retentez votre chance jusqu'au <b>".strftime("%A %d %B %G",strtotime($end_date))."</b>.</p>
                        <form class='mqc_text_align_center' method='get'>
                        <input type='submit' name='' value='Revenir au jeu'>";
                }
            }
        // Le joueur a cliqué sur "PARTICIPER" etape 2/4-----------------------------------------------------------------
    }elseif(isset($_GET['participate'])){
        global $wpdb;
        $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "mqc_plugin_game_settings ORDER BY id DESC LIMIT 1");
        $results = $wpdb->get_results($query);
        foreach ($results as $result){
            echo "<p id='mqc_heading_2'class='mqc_p'>Répondez correctement aux questions  :</p>
                <form method='get'>
                    <label for='question_1' class='mqc_align' ><b>$result->question_1 </b>:</label><br>
                    <input type='radio' name='answer_1' value='$result->answer_1_2' id='answer_1_2' class='mqc_styled' /><label for=''>$result->answer_1_2</label><br>
                    <input type='radio' name='answer_1' value='$result->answer_1_1' id='answer_1_1' class='mqc_styled' /><label for=''>$result->answer_1_1</label><br>
                    <input type='radio' name='answer_1' value='$result->answer_1_3' id='answer_1_3' class='mqc_styled' /><label for=''>$result->answer_1_3</label><br>

                    <label for='question_1' class='mqc_align' ><b>$result->question_2 </b>:</label><br>
                    <input type='radio' name='answer_2' value='$result->answer_2_1' id='answer_2_1' class='mqc_styled'/><label for=''>$result->answer_2_1</label><br>
                    <input type='radio' name='answer_2' value='$result->answer_2_2' id='answer_2_2' class='mqc_styled'/><label for=''>$result->answer_2_2</label><br>
                    <input type='radio' name='answer_2' value='$result->answer_2_3' id='answer_2_3' class='mqc_styled'/><label for=''>$result->answer_2_3</label><br>
                    
                    <label for='question_1' class='mqc_align' ><b>$result->question_3 </b>:</label><br>
                    <input type='radio' name='answer_3' value='$result->answer_3_2' id='answer_3_2' class='mqc_styled'/><label for=''>$result->answer_3_2</label><br>
                    <input type='radio' name='answer_3' value='$result->answer_3_3' id='answer_3_3' class='mqc_styled'/><label for=''>$result->answer_3_3</label><br>
                    <input type='radio' name='answer_3' value='$result->answer_3_1' id='answer_3_1' class='mqc_styled'/><label for=''>$result->answer_3_1</label><br>

                    <div class='mqc_text_align_center'>
                    <input type='submit' name='submit_answer' value='Envoyer' id='mqc_button_envoyer'/>
                    <p id='mqc_heading_readrules' class='mqc_light'>En cliquant sur envoyer vous acceptez et reconnaissez avoir lus le règlement du jeu.</p>
                    </div>
                </form>

                <form class='mqc_text_align_center' method='get'>
                <input id='mqc_link_button' type='submit' name='' value='Revenir au jeu' class='mqc_input_front'>
                </form>";}

    }elseif(isset($_GET['submit_answer'])){
        global $wpdb;
        $table = $wpdb->prefix . "mqc_plugin_game_settings";
        $query = $wpdb ->prepare("SELECT * FROM $table ORDER BY id DESC LIMIT 1 ");
        $results = $wpdb->get_results($query);
        foreach($results as $result){

        $correct_answer_1 = $result->answer_1_1;
        $correct_answer_2 = $result->answer_2_1;
        $correct_answer_3 = $result->answer_3_1;
        }
        // Si les réponses sont bonnes direction /send_data_correct/ :
        if($_GET['answer_1']==$correct_answer_1 and $_GET['answer_2']==$correct_answer_2 and $_GET['answer_3']==$correct_answer_3){
            echo "<p id='mqc_heading_2' class='mqc_p'>Veuillez renseigner les informations suivantes:</p>
            <form method='post'>
                <label for='Civilité' >Civilité* :</label> 
                <input type='radio' name='user_gender' value='M' id='M_gender'  /><label for='M'>Monsieur</label>
                <input type='radio' name='user_gender' value='F' id='F_gender' checked='checked' /><label for='F'>Madame</label><br>
                
                <label for='user_name'>Nom* :</label><input type='text' name='user_name' id='user_name' class='mqc_input_front' required/><br>
                <label for='user_firstname'>Prénom* :</label><input type='text' name='user_firstname' id='user_firstname' class='mqc_input_front' required/><br>
                <label for='user_email'>Email* :</label><input type='email' name='user_email' id='user_email' class='mqc_input_front' required/><br>
                <label for='user_birthdate'>Date de naissance* :</label><input type='date' name='user_birthdate' id='user_birthdate' align='center' min='1900-01-01' max='2000-01-01' class='mqc_input_front' required/><br>
                <script>
                    var today = new Date();
                    var dd = today.getDate();
                    var mm = today.getMonth()+1; //January is 0!
                    var yyyy = today.getFullYear()-18;
                    if(dd<10){
                            dd='0'+dd
                        } 
                        if(mm<10){
                            mm='0'+mm
                        } 
                    today = yyyy+'-'+mm+'-'+dd;
                    document.getElementById('user_birthdate').setAttribute('max', today);
                </script>
                <label for='user_address'>Adresse* :</label><input type='text' name='user_address' id='user_address' class='mqc_input_front' required/><br>
                <br>
                <p class='mqc_light'>*Champs obligatoires</p>
                <div class='mqc_text_align_center'>
                    <input type='submit' name='send_data_correct' value='Envoyer' id='mqc_button_envoyer' class='mqc_input_front'/>
                    <p id='mqc_heading_readrules' class='mqc_light'>En cliquant sur envoyer vous acceptez et reconnaissez avoir lus le règlement du jeu.</p>
                </div>
            </form>
            
            <form class='mqc_text_align_center' method='get'>
                <input id='mqc_link_button' type='submit' name='' value='Revenir au jeu' class='mqc_input_front'>
            </form>";
        
        // Si les réponses sont fausses direction /send_data_false/ :
        }else{
        echo "<p id='mqc_heading_2' class='mqc_p'>Veuillez renseigner les informations suivantes:</p>
            <form method='post'>
                <label for='Civilité' >Civilité* :</label> 
                <input type='radio' name='user_gender' value='M' id='M_gender'  /><label for='M'>Monsieur</label>
                <input type='radio' name='user_gender' value='F' id='F_gender' checked='checked' /><label for='F'>Madame</label><br>
                
                <label for='user_name'>Nom* :</label><input type='text' name='user_name' id='user_name' class='mqc_input_front' required/><br>
                <label for='user_firstname'>Prénom* :</label><input type='text' name='user_firstname' id='user_firstname' class='mqc_input_front' required/><br>
                <label for='user_email'>Email* :</label><input type='email' name='user_email' id='user_email' class='mqc_input_front' required/><br>
                <label for='user_birthdate'>Date de naissance* :</label><input type='date' name='user_birthdate' id='user_birthdate' align='center' min='1900-01-01' max='2000-01-01' class='mqc_input_front' required/><br>
                <script>
                    var today = new Date();
                    var dd = today.getDate();
                    var mm = today.getMonth()+1; //January is 0!
                    var yyyy = today.getFullYear()-18;
                    if(dd<10){
                            dd='0'+dd
                        } 
                        if(mm<10){
                            mm='0'+mm
                        } 
                    today = yyyy+'-'+mm+'-'+dd;
                    document.getElementById('user_birthdate').setAttribute('max', today);
                </script>

                <label for='user_address'>Adresse* :</label><input type='text' name='user_address' id='user_address' class='mqc_input_front' required/><br>
                <br>
                <p class='mqc_light'>*Champs obligatoires</p>
                <div class='mqc_text_align_center'>
                    <input type='submit' name='send_data_false' value='Envoyer' id='mqc_button_envoyer' class='mqc_input_front'/>
                    <p id='mqc_heading_readrules' class='mqc_light'>En cliquant sur envoyer vous acceptez et reconnaissez avoir lus le règlement du jeu.</p>
                </div>
            </form>";
            }
    // //  OU le joueur a cliqué sur "VOIR LE REGLEMENT" ----------------------------------------------------------------- 
    }elseif(isset($_GET['rules'])){
        global $wpdb;
        $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "mqc_plugin_game_settings ORDER BY id DESC LIMIT 1");
        $results = $wpdb->get_results($query);
        foreach($results as $result){
        
            $start_date=$result->start_date;
            $end_date=$result->end_date;
            $gifts=$result->gifts;
            $company_name=$result->company_name;
            $company_address=$result->company_address;
            $web_site=$result->web_site;
            
            setlocale (LC_TIME, 'fr_FR','fra');

            echo "<p id='mqc_heading_rules' class='mqc_p'>Règlement</p><br><hr><br>
                
                <h3> ARTICLE 1 - ORGANISATEUR ET DUREE DU JEU-CONCOURS<h3>
                <p>Le présent jeu-concours est organisé par <b>$company_name</b> ,
                domicilié(e) <b>$company_address</b> , désigné ci-après
                « l’Organisateur ».
                Le jeu-concours se déroulera du <b>".strftime("%A %d %B %G",strtotime($start_date))." minuit</b> au <b>".strftime("%A %d %B %G",strtotime($end_date))." minuit</b> 
                (date et heure française de connexion faisant foi).</p>;
            
                <h3>ARTICLE 2 - CONDITIONS DE PARTICIPATION AU JEU-CONCOURS<h3>
                <p>Le jeu-concours est ouvert à toute personne physique résidant en France
                métropolitaine. Les participants doivent être âgés de plus de 18 ans.
                Le jeu-concours est limité à une seule participation par personne (par
                exemple même nom, même prénom et même adresse email). La participation
                au jeu-concours est strictement personnelle et nominative. Il ne sera
                attribué qu’un seul lot par personne désignée gagnante.
                La participation au jeu-concours implique l’acceptation irrévocable et sans
                réserve, des termes et conditions du présent règlement (le « Règlement »),
                disponible sur le site:  <b>$web_site</b> .
                Le non-respect des conditions de participation énoncées dans le présent
                Règlement entraînera la nullité de la participation du Participant.
                Le jeu est soumis à la réglementation de la loi française applicable aux jeux
                et concours.</p>
    
                <h3>ARTICLE 3 - PRINCIPE DE JEU / MODALITES DE PARTICIPATION<h3>
                <p>Pour valider sa participation, chaque participant doit dûment s’inscrire répondant à
                un formulaire mis en ligne, avant la fermeture du jeu. Chaque internaute en
                s’inscrivant au jeu obtient une chance d’être tiré au sort à condition d'avoir répondu correctement aux 3 questions du formulaire.</p>
                
                <h3>ARTICLE 4 – DÉSIGNATION DES GAGNANTS<h3>
                <p>L’Organisateur désignera par tirage au sort les gagnants, parmi l’ensemble des
                personnes ayant correctement répondu au questionnaire et s’étant inscrites. Un tirage au sort sera effectué le   <b>".strftime("%A %d %B %G",strtotime($end_date)).".</b> Un seul lot sera attribué par gagnant (même nom, même prénom, même adresse
                email)</p>
            
                <h3>ARTICLE 5 – DOTATIONS<h3>
                <p>Les dotations des tirages au sort sont les suivantes :
                <b>$gifts</b>  </p>
                
                <h3>ARTICLE 6 - REMISE DES DOTATIONS ET MODALITÉS
                D’UTILISATION DES DOTATIONS<h3>
                <p>L’Organisateur du jeu-concours contactera uniquement par courrier électronique les
                Gagnants tirés au sort et les informera de leur dotation et des modalités à suivre
                pour y accéder. Aucun courrier ne sera adressé aux participants n’ayant pas gagné,
                seuls les gagnants seront contactés. Les gagnants devront répondre dans les deux
                (2) jours suivants l’envoi de ce courrier électronique et fournir leurs coordonnées
                complètes. Sans réponse de la part du gagnant dans les deux (2) jours suivants
                l’envoi de ce courrier électronique, il sera déchu de son lot et ne pourra prétendre à
                aucune indemnité, dotation ou compensation que ce soit. Dans cette hypothèse, les
                lots seront attribués à un suppléant désignés lors d'un tirage au sort parmis les participants.
                Les gagnants devront se conformer au présent règlement. S’il s’avérait
                qu’ils ne répondent pas aux critères du présent règlement, leur lot ne leur sera pas
                attribué et sera acquis par l’Organisateur. À cet effet, les participants autorisent
                toutes les vérifications concernant leur identité, leur âge, leurs coordonnées ou la
                loyauté et la sincérité de leur participation. Toute fausse déclaration, indication
                d’identité ou d’adresse postale fausse entraîne l’élimination immédiate du
                participant et l’acquisition du lot par l’Organisateur. En outre, en cas d’impossibilité
                pour l’Organisateur de délivrer au(x) gagnant(s) la dotation remportée, et ce, quel
                qu’en soit la cause, L’Organisateur se réserve le droit d’y substituer une dotation de
                valeur équivalente, ce que tout participant consent. </p>
    
                <h3>ARTICLE 7 – UTILISATION DES DONNÉES PERSONNELLES DES
                PARTICIPANTS<h3>
                <p>Conformément à la loi Informatique et Libertés du 6 janvier 1978, les participants
                au jeu concours bénéficient auprès de l’Organisateur, d’un droit d’accès, de
                rectification (c’est à-dire de complément, de mise à jour et de verrouillage) et de
                retrait de leurs données personnelles. Les informations personnelles des
                participants sont collectées par l’Organisateur uniquement à des fins de suivi du
                jeu-concours, et sont indispensables pour participer à celle-ci.</p>
            
                <h3>ARTICLE 8 – RESPONSABILITÉ<h3>
                <p>L’Organisateur ne saurait voir sa responsabilité engagée du fait de
                l’impossibilité de contacter chaque gagnant, de même qu’en cas de perte, de
                vol ou de dégradation du lot lors de son acheminement. L’Organisateur ne
                pourra non plus être responsable des erreurs éventuelles portant sur le nom,
                l’adresse et/ou les coordonnées communiquées par les personnes ayant
                participé au jeu-concours. Par ailleurs, l’Organisateur du jeu concours décline
                toute responsabilité pour tous les incidents qui pourraient survenir lors de la
                jouissance du prix attribué et/ou fait de son utilisation et/ou de ses
                conséquences, notamment de la jouissance d’un lot par un mineur, qui reste
                sous l’entière et totale responsabilité d’une personne ayant l’autorité
                parentale. L’Organisateur se réserve le droit, si les circonstances l’exigent,
                d’écourter, de prolonger, de modifier, d’interrompre, de différer ou
                d’annuler le jeu-concours, sans que sa responsabilité ne soit engagée.
                Toutefois, toute modification fera l’objet d’un avenant qui sera mis en ligne
                sur le Site et adressé gratuitement à toute personne ayant fait une demande
                de règlement par écrit conformément aux dispositions de l’article 10 cidessous. 
                L’Organisateur se dégage de toute responsabilité en cas de
                dysfonctionnement empêchant l’accès et/ ou le bon déroulement du jeuconcours 
                notamment dû à des actes de malveillances externes. L’utilisation
                de robots ou de tous autres procédés similaires permettant de participer au
                jeu-concours de façon mécanique ou autre est proscrite, la violation de cette
                règle entraînant l’élimination définitive de son réalisateur et/ ou utilisateur.
                L’Organisateur pourra annuler tout ou partie du jeu-concours s’il apparaît que
                des fraudes sont intervenues sous quelque forme que ce soit, notamment de
                manière informatique dans le cadre de la participation au jeu-concours ou de
                la détermination des gagnants. Il se réserve, dans cette hypothèse, le droit
                de ne pas attribuer les dotations aux fraudeurs et/ ou de poursuivre devant
                les juridictions compétentes les auteurs de ces fraudes. 
                </p>
                
                <h3>ARTICLE 9 – ACCESSIBILITÉ DU RÈGLEMENT<h3>
                <p>Le règlement peut être consulté librement depuis le site   <b>$web_site</b>   à tout moment ou encore,
                envoyé gratuitement par
                l’Organisateur sur simple demande écrite émanant de tout participant en
                écrivant à l’adresse postale du jeu-concours visible à l’article 10 du présent
                règlement. Le participant souhaitant obtenir le remboursement des frais
                postaux liés à cette demande de règlement, doit le préciser dans sa demande
                (remboursement sur la base d’une lettre simple de moins de 20 g affranchie
                au tarif économique en vigueur).</p> 
    
                <h3>ARTICLE 10 – ADRESSE POSTALE DU JEU-CONCOURS<h3>
                <p>Pour toute demande, l’adresse postale destinataire des courriers est
                mentionnée ci-après :   <b>$company_name  -   $company_address</b>  .</p>
                
                <h3>ARTICLE 11 – LOI APPLICABLE<h3>
                <p>Les participants admettent sans réserve que le simple fait de participer à ce
                jeu concours les soumet à la loi française. Toute contestation doit être
                adressée à l’adresse mentionnée dans l’article 10 au plus tard le   <b>".strftime("%A %d %B %G",strtotime($end_date))."</b> 
                inclus (cachet de la poste faisant foi).</p>
            </div>
            <form method='get'>
            <input id='mqc_link_button' type='submit' name='' value='Revenir au jeu'>";
            }
    // Le joueur n'a pas encore cliqué sur "PARTICIPER" etape 1/3-----------------------------------------------------------------
    }else{
        global $wpdb;
        $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "mqc_plugin_game_settings ORDER BY id DESC LIMIT 1");
        $results = $wpdb->get_results($query);
        foreach($results as $result){
            $end_date = $result->end_date;
            setlocale (LC_TIME, 'fr_FR','fra'); 
            
            
            echo "
                <p id='mqc_heading_1' class='mqc_p'>Tentez de gagner <b>".$result->gifts."</b>!</p><br>
                <p class='mqc_p'> Pour jouer, c’est très simple:
                <br>Inscrivez-vous et <b>répondez à notre quiz</b> ! <b><br>".$result->winners_nbr."</b> gagnant(s) sont/seront selectionné(s)<br>
                <p class='mqc_p'>Tirage au sort le <b>".strftime("%A %d %B %G",strtotime($end_date))."</b><br><br>

                Allez-y,  jouez et bonne chance à vous !</p><br>";
            echo " <form class='mqc_text_align_center' method='get'>
                <input type='submit' name='participate' value='Participer'>
                </form>
                <form class='mqc_text_align_center' method='get'>
                <input id='mqc_link_button' type='submit' name='rules' value='Voir le réglement'>
                </form>";}
    }
    echo "</div class='wrap'>";
}
       
// *******************************************************************************************************************************************************************************************************************************************************************
// ******************************************************************************************************* BACK OFFICE ************************************************************************************************************************************************************
// *******************************************************************************************************************************************************************************************************************************************************************

/*
 * Ajout d'un nouveau menu dnas l'administration du backoffice de WP
 */

 // Récupération du Hook action 'admin_menu', exécution de la fonction: 'mqc_Add_My_Admin_Link()'
add_action( 'admin_menu', 'mqc_Add_My_Admin_Link' );

function mqc_Add_My_Admin_Link(){
    add_menu_page(
        'réglage', // Title of the page
        'Mon quiz concours', // Text to show on the menu link
        'manage_options', // Capability requirement to see the link
        'includes/backoffice.php', // The 'slug' - file to display when clicking the link
        'mqc_backoffice_fctn'
    );
}

function mqc_backoffice_fctn(){
    // ****************** Générer ou modifier votre jeux-concours ***************************************************
    // Connection a la BDD
    echo "<div class='wrap'>";
    global $wpdb;
    // Le client a cliqué sur "ENVOYER" -> les données sont enregistrées dans la BDD
    if(isset($_POST['game_settings_bdd_data'])){
        echo "
            <div class='mqc_text_align_center'>
                <div id='mqc_backoffice_label'>
                    <h1>Mon quiz concours - réglages</h1>
                    <h2>Générer ou modifier votre quiz-concours:</h2>
                    <p class='mqc_color_white'>Placez votre jeu-concours grace au shortcode : <b>[mon_quiz_concours]</b>.</p>
                </div>
                <p class='mqc_color_red'>Attention votre règlement et les règles du jeux visibles par les participants sont etablis dans ces champs :</p>

                <hr>

                <form  method='post'>
                    <p>La date de tirage au sort ne peut pas etre anterieur à celle de mise en place et à la date actuelle.</p>
                    <label for='start_date'>Date de mise en place : </label><input class='mqc_input' type='date' name='start_date' id='start_date' required/><br>
                    <label for='end_date'>Date du tirage au sort : </label><input class='mqc_input' type='date' name='end_date' id='end_date' required/><br>
                    <p>Placez un article indefini ou un chiffre avant le gain : exemple une voiture / 1 voiture.</p>
                    <label for='gifts'>Lots à gagner :</label><input type='text' name='gifts' id='gifts' class='mqc_input_medium mqc_input' required/><br>
                    <p>Definissez ici le nombre de gagnants que l'ordinateur choisira au hasard parmis la liste des participants.</p>
                    <label for='winners_nbr'>Nombre de gagnants :</label><input type='number' name='winners_nbr' min='1' id='winners_nbr' class='mqc_input_small mqc_input' required/><br>
                    <br>
                    <p>Les informations suivantes seront visibles dans le règlement de votre jeu concours:</p>
                    <label for='company_name'>Votre nom ou marque: </label><input type='text' name='company_name' id='company_name' class='mqc_input_large mqc_input' required/><br>
                    <label for='company_address'>Votre adresse postale </label><input type='text' name='company_address' id='company_address' class='mqc_input_large mqc_input' required/><br>
                    <label for='web_site'>L'adresse URL final de votre site :</label><input type='text' name='web_site' id='web_site' class='mqc_input_large mqc_input' required/><br><br>
                    <hr>
                    <p>Les questions et réponses de votre jeu concours:</p>
                    <label for='question_1'>Votre question numero 1: </label><input type='text' name='question_1' id='question_1' class='mqc_input_large mqc_input' required/><br>
                    <label class='mqc_color_green' for='answer_1_1'>Bonne réponse: </label><input type='text' name='answer_1_1' id='answer_1_1' class=' mqc_input' required/>
                    <label for='answer_1_2'>Mauvaise réponse 1: </label><input type='text' name='answer_1_2' id='answer_1_2' class=' mqc_input' required/>
                    <label for='answer_1_3'>Mauvaise réponse 2: </label><input type='text' name='answer_1_3' id='answer_1_3' class=' mqc_input' required/><br><br>

                    <label for='question_2'>Votre question numero 2: </label><input type='text' name='question_2' id='question_2' class='mqc_input_large mqc_input' required/><br>
                    <label class='mqc_color_green' for='answer_2_1'>Bonne réponse: </label><input type='text' name='answer_2_1' id='answer_2_1' class=' mqc_input' required/>
                    <label for='answer_2_2'>Mauvaise réponse 1: </label><input type='text' name='answer_2_2' id='answer_2_2' class=' mqc_input' required/>
                    <label for='answer_2_3'>Mauvaise réponse 2: </label><input type='text' name='answer_2_3' id='answer_2_3' class=' mqc_input' required/><br><br>

                    <label for='question_3'>Votre question numero 3: </label><input type='text' name='question_3' id='question_3' class='mqc_input_large mqc_input' required/><br>
                    <label class='mqc_color_green' for='answer_3_1'>Bonne réponse: </label><input type='text' name='answer_3_1' id='answer_3_1' class=' mqc_input' required/>
                    <label for='answer_3_2'>Mauvaise réponse 1: </label><input type='text' name='answer_3_2' id='answer_3_2' class=' mqc_input' required/>
                    <label for='answer_3_3'>Mauvaise réponse 2: </label><input type='text' name='answer_3_3' id='answer_3_3' class=' mqc_input' required/><br>
                    <p class='mqc_color_red'>Attention: si vous reinitialisez les questions et les réponses vous laisserez les joueurs qui ont répondu correctement aux questions précedentes dans la liste des inscrits au tirage au sort.</p>
                    <input type='submit' name='game_settings_bdd_data' value='Envoyer' class='button button-primary' />
                </form>
            </div>";

        $today = date("Y-m-d");

        //controle des date de depart et tirage au sort
        if ($_POST['end_date']<=$_POST['start_date']){
        
            echo "<script>alert(\"La date de tirage au sort ne peut pas etre anterieur à celle de mise en place\")</script>";
            
        } elseif($_POST['end_date']<= $today ){
            echo "<script>alert(\"La date de tirage au sort ne peut pas etre anterieur à celle actuelle\")</script>";
            
        } else{
        // execution avec les valeur fournies par la methode POST
            $table = $wpdb->prefix."mqc_plugin_game_settings"; ;
            $datas = array(
            'start_date'=>$_POST['start_date'],
            'end_date'=>$_POST['end_date'],
            'gifts'=>$_POST['gifts'],
            'winners_nbr'=>$_POST['winners_nbr'],
            'company_name'=>$_POST['company_name'],
            'company_address'=>$_POST['company_address'],
            'web_site'=>$_POST['web_site'],
            'question_1'=>$_POST['question_1'],
            'answer_1_1'=>$_POST['answer_1_1'],
            'answer_1_2'=>$_POST['answer_1_3'],
            'answer_1_3'=>$_POST['answer_1_2'],
            'question_2'=>$_POST['question_2'],
            'answer_2_1'=>$_POST['answer_2_1'],
            'answer_2_2'=>$_POST['answer_2_3'],
            'answer_2_3'=>$_POST['answer_2_2'],
            'question_3'=>$_POST['question_3'],
            'answer_3_1'=>$_POST['answer_3_1'],
            'answer_3_2'=>$_POST['answer_3_3'],
            'answer_3_3'=>$_POST['answer_3_2']
            );
            $wpdb -> insert($table,$datas,$array);    
        }
    // le client n'a pas appuyé sur "ENVOYER" -> le formlaire s'affiche + les donées déja enregistrés
    }else{
        echo "
            <div class='mqc_text_align_center'>
                <div id='mqc_backoffice_label'>
                    <h1>Mon quiz concours - réglages</h1>
                    <h2>Générer ou modifier votre quiz-concours:</h2>
                    <p class='mqc_color_white' >Placez votre jeu-concours grace au shortcode : <b>[mon_quiz_concours]</b>.</p>
                </div>
                <p class='mqc_color_red'>Attention votre règlement et les règles du jeux visibles par les participants sont etablis dans ces champs :</p>

                <hr>
                <form method='post'>
                    <p>La date de tirage au sort ne peut pas etre anterieur à celle de mise en place et à la date actuelle.</p>
                    <label for='start_date'>Date de mise en place : </label><input class='mqc_input' type='date' name='start_date' id='start_date' required/><br>
                    <label for='end_date'>Date du tirage au sort : </label><input class='mqc_input' type='date' name='end_date' id='end_date' required/><br>
                    <p>Placez un article indefini ou un chiffre avant le gain : exemple une voiture / 1 voiture.</p>
                    <label for='gifts'>Lots à gagner :</label><input type='text' name='gifts' id='gifts' class='mqc_input_medium mqc_input' required/><br>
                    <p>Definissez ici le nombre de gagnants que l'ordinateur choisira au hasard parmis la liste des participants.</p>
                    <label for='winners_nbr'>Nombre de gagnants :</label><input type='number' min='1' name='winners_nbr' id='winners_nbr' class='mqc_input_small mqc_input' required/><br>
                    <br>
                    <p>Les informations suivantes seront visibles dans le règlement de votre jeu concours:</p>
                    <label for='company_name'>Votre nom ou marque: </label><input type='text' name='company_name' id='company_name' class='mqc_input_large mqc_input' required/><br>
                    <label for='company_address'>Votre adresse postale </label><input type='text' name='company_address' id='company_address' class='mqc_input_large mqc_input' required/><br>
                    <label for='web_site'>L'adresse URL final de votre site :</label><input type='text' name='web_site' id='web_site' class='mqc_input_large mqc_input' required/><br><br>
                    <hr>
                    <p>Les questions et réponses de votre jeu concours:</p>
                    <label for='question_1'>Votre question numero 1: </label><input type='text' name='question_1' id='question_1' class='mqc_input_large mqc_input' required/><br>
                    <label class='mqc_color_green' for='answer_1_1'>Bonne réponse: </label><input type='text' name='answer_1_1' id='answer_1_1' class=' mqc_input' required/>
                    <label for='answer_1_2'>Mauvaise réponse 1: </label><input type='text' name='answer_1_2' id='answer_1_2' class=' mqc_input' required/>
                    <label for='answer_1_3'>Mauvaise réponse 2: </label><input type='text' name='answer_1_3' id='answer_1_3' class=' mqc_input' required/><br><br>

                    <label for='question_2'>Votre question numero 2: </label><input type='text' name='question_2' id='question_2' class='mqc_input_large mqc_input' required/><br>
                    <label class='mqc_color_green' for='answer_2_1'>Bonne réponse: </label><input type='text' name='answer_2_1' id='answer_2_1' class=' mqc_input' required/>
                    <label for='answer_2_2'>Mauvaise réponse 1: </label><input type='text' name='answer_2_2' id='answer_2_2' class=' mqc_input' required/>
                    <label for='answer_2_3'>Mauvaise réponse 2: </label><input type='text' name='answer_2_3' id='answer_2_3' class=' mqc_input' required/><br><br>

                    <label for='question_3'>Votre question numero 3: </label><input type='text' name='question_3' id='question_3' class='mqc_input_large mqc_input' required/><br>
                    <label class='mqc_color_green' for='answer_3_1'>Bonne réponse: </label><input type='text' name='answer_3_1' id='answer_3_1' class=' mqc_input' required/>
                    <label for='answer_3_2'>Mauvaise réponse 1: </label><input type='text' name='answer_3_2' id='answer_3_2' class=' mqc_input' required/>
                    <label for='answer_3_3'>Mauvaise réponse 2: </label><input type='text' name='answer_3_3' id='answer_3_3' class=' mqc_input' required/><br>
                    <p class='mqc_color_red'>Attention: si vous reinitialisez les questions et les réponses vous laisserez les joueurs qui ont répondu correctement aux questions précedentes dans la liste des inscrits au tirage au sort.</p>
                    <br><input type='submit' name='game_settings_bdd_data' value='Envoyer' class='button button-primary mqc_input' />
                </form><hr> 
            </div>"; 
    }
    // ******************************** Données enregistrées *****************************

    echo "<div class='mqc_text_align_center'>
            <h2>Vos paramètres enregistrés: </h2>
        </div>";
    //  Affichage des données enregistrées dans la bdd, on demande uniquement la dernier entrée de la table en cas de mise a jour des données par l'utlisateur
    // global de la base de données de WP
    global $wpdb;
    // on prepare la requete pour contrer les injection SQL
    $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "mqc_plugin_game_settings ORDER BY id DESC LIMIT 1");
    // mets les resultats de la requete dans une variable $results 
    $results = $wpdb->get_results($query);
    if (isset($results)){
        foreach ($results as $result){
            echo "<p class='mqc_text_align_center'>
                Date de mise en place prévue: <b>". $result->start_date ."</b><br>
                Date du tirage au sort prévue: <b>".$result->end_date.'</b><br>
                Gains en jeux: <b>'.$result->gifts."</b><br>
                Nombres de gagnants prévus: <b>".$result->winners_nbr."</b><br>
                <br>Votre <b>question n°1</b> : <b>".$result->question_1."</b><br> Réponse 1 (la bonne réponse): <b>".$result->answer_1_1."</b> - Réponse 2 : ".$result->answer_1_2." - Réponse 3 : ".$result->answer_1_3."<br>
                <br>Votre <b>question n°2</b> : <b>".$result->question_2."</b><br> Réponse 1 (la bonne réponse): <b>".$result->answer_2_1."</b> - Réponse 2 : ".$result->answer_2_2." - Réponse 3 : ".$result->answer_2_3."<br>
                <br>Votre <b>question n°3</b> : <b>".$result->question_3."</b><br> Réponse 1 (la bonne réponse): <b>".$result->answer_3_1."</b> - Réponse 2 : ".$result->answer_3_2." - Réponse 3 : ".$result->answer_3_3."<br>
             </p>
             <hr>";
        } 
    }
    
    // ******************************** tirage au sort*****************************
    echo "<div class='mqc_text_align_center'>
            <h2>Le tirage au sort : </h2>";
   
    // Recuperaton du nombre de gagnants designé dans la bdd afin de lancer la requete qui selectionne au hasard le nombre ($winners) de gagnants.
    global $wpdb;
    $query = $wpdb->prepare("SELECT winners_nbr FROM " . $wpdb->prefix . "mqc_plugin_game_settings ORDER BY id DESC LIMIT 1");
    $results = $wpdb->get_results($query);

    foreach($results as $result){
    
        $winners= $result->winners_nbr;
    }
    
    if(isset($_POST['winner_check'])){

    // Selection au hassar par la fonction RAND(), et nombre de gagnants
        global $wpdb;
        $table_user = $wpdb->prefix . "mqc_plugin_user";
        $query = $wpdb->prepare("SELECT * FROM $table_user WHERE user_answer_success = 'correct' ORDER BY RAND() LIMIT $winners");
        $results = $wpdb->get_results($query);
        foreach($results as $result){
                echo "<div id='mqc_winner_background' ><p>Votre grand gagnant est :<br><br> <b>$result->user_firstname
                $result->user_name</b>(
                $result->user_gender)<br>né(e) le <b>
                $result->user_birthdate</b><br>Adresse: <b>
                $result->user_address</b><br>Email: <b>
                $result->user_email</b><br>Date d'inscription: <b> 
                $result->user_timestamp</b><br>Joueur numero: <b>
                $result->id</b></p></div>";}   
    }
    echo "<p class='mqc_color_red'>Attention : cliquez sur <b>Tirage au sort</b> uniquement lorsque vous <b>décidez de cloturer</b> votre concours et une seule une fois!</p>
        <p>
            <form method='post'>
            <input type='submit' name='winner_check' value='Tirage au sort' class='button button-primary'>
            </form>
        </p>
        </div>";

    // ******************************** Affichage des participants *****************************
   
    global $wpdb;
    $wpdb->get_results(" SELECT * FROM ". $wpdb->prefix . "mqc_plugin_user");
    echo "
        <p class='mqc_text_align_center'>Nombre total de participants : <b>$wpdb->num_rows</b>
        </p>
        ";
    $wpdb->get_results(" SELECT * FROM ". $wpdb->prefix . "mqc_plugin_user WHERE user_answer_success = 'false' ");
    echo "
        <p class='mqc_text_align_center'>Nombre total de mauvaises réponses : <b>$wpdb->num_rows</b></p>
        ";
    $wpdb->get_results(" SELECT * FROM ". $wpdb->prefix . "mqc_plugin_user WHERE user_answer_success = 'correct' ");
    echo "
        <p class='mqc_text_align_center'>Nombre total de bonnes réponses : <b>$wpdb->num_rows</b><br></p>
        ";
    
    
    
    echo "
    <div class='mqc_text_align_center'>
    <hr><h2>Liste de tous les participants(es) à votre jeu-concours :</h2>
    </div>
    ";
    
    global $wpdb;
    $query = $wpdb->prepare(" SELECT * FROM ". $wpdb->prefix . "mqc_plugin_user ORDER by id");
    $results = $wpdb->get_results($query);

   
    echo "
        <p><table id='contributor' align='center'> 
        <th>Numéro -</th> <th>Date et heure d'inscription - </th> <th>Civilité - </th> <th>Nom - </th> <th>Prénom - </th> <th>Email - </th> <th>Date d'anniversaire - </th> <th>Adresse</th>
        ";
        foreach($results as $result){
            echo'<tr>';
            echo '<td>'.$result->id.'</td><td>'.$result->user_timestamp.'</td><td>'.$result->user_gender.'</td><td>'
                .$result->user_name.'</td><td>'.$result->user_firstname.'</td><td>'.$result->user_email.'</td>'.'</td><td>'
                .$result->user_birthdate.'</td><td>'.$result->user_address; 
            echo'</tr>';
        }
        echo '</table></p>'; 
    die();
    echo "</div class='wrap'>";
}

    // <!- ************************* copyright ***************************************
    // ************** Plugin:  Mon qiz concours par Frederic Castel *************
    // ******************************************************************************** -->

