const translations = {
  fr: {
    nav_home: "Accueil", nav_campaigns: "Cagnottes", nav_how: "Comment ça marche",
    nav_testimonials: "Témoignages", nav_login: "Connexion", nav_register: "Inscription",
    nav_dashboard: "Mon espace", nav_logout: "Déconnexion",
    hero_title: "Collectons Ensemble", hero_subtitle: "Créez votre cagnotte, partagez votre cause et recevez des dons via Mobile Money en toute sécurité.",
    hero_cta1: "Créer ma cagnotte", hero_cta2: "Voir les cagnottes",
    stats_campaigns: "Cagnottes créées", stats_donors: "Donateurs",
    stats_satisfaction: "de satisfaction", stats_collected: "FCFA collectés",
    how_title: "Comment ça marche ?", how_step1_title: "Créez", how_step1_desc: "Créez votre cagnotte en quelques minutes avec une description et un objectif.",
    how_step2_title: "Partagez", how_step2_desc: "Partagez votre lien unique sur WhatsApp, Facebook, Telegram.",
    how_step3_title: "Collectez", how_step3_desc: "Recevez les dons via Mobile Money et retirez votre argent facilement.",
    featured_title: "Cagnottes terminées", featured_subtitle: "Des projets financés avec succès grâce à votre générosité",
    testimonials_title: "Ce qu'ils disent de nous",
    categories_title: "Explorez par catégorie",
    cat_health: "Santé", cat_education: "Éducation", cat_emergency: "Urgence",
    cat_projects: "Projets", cat_humanitarian: "Humanitaire", cat_religion: "Religion",
    cat_sports: "Sports", cat_arts: "Arts & Culture",
    donate_btn: "Faire un don", share_btn: "Partager",
    collected: "collecté", goal: "objectif", donors: "donateurs",
    footer_desc: "Plateforme de crowdfunding sécurisée pour l'Afrique francophone.",
    footer_links: "Liens utiles", footer_legal: "Mentions légales", footer_privacy: "Confidentialité",
    footer_contact: "Contact", footer_rights: "Tous droits réservés.",
    login_title: "Connexion", login_email: "Email ou téléphone", login_password: "Mot de passe",
    login_btn: "Se connecter", login_no_account: "Pas encore de compte ?",
    login_forgot: "Mot de passe oublié ?",
    register_title: "Créer un compte", register_name: "Nom complet", register_email: "Adresse email",
    register_phone: "Numéro de téléphone", register_password: "Mot de passe",
    register_confirm: "Confirmer le mot de passe", register_btn: "S'inscrire",
    register_have_account: "Déjà un compte ?",
    dashboard_welcome: "Bienvenue", dashboard_balance: "Solde disponible",
    dashboard_pending: "En attente", dashboard_collected: "Total collecté",
    dashboard_donations_received: "Dons reçus",
    kyc_title: "Vérification d'identité", kyc_desc: "Obligatoire avant tout retrait.",
    kyc_front: "Recto carte d'identité", kyc_back: "Verso carte d'identité",
    kyc_selfie: "Selfie avec carte", kyc_submit: "Soumettre",
    kyc_pending: "En attente de validation", kyc_approved: "Identité vérifiée",
    kyc_rejected: "Refusé",
    withdraw_title: "Demande de retrait", withdraw_amount: "Montant souhaité",
    withdraw_commission: "Commission (10%)", withdraw_net: "Vous recevez",
    withdraw_number: "Numéro Mobile Money", withdraw_operator: "Opérateur",
    withdraw_submit: "Demander le retrait",
    create_campaign: "Créer une cagnotte", campaign_title: "Titre",
    campaign_desc: "Description", campaign_goal: "Objectif (FCFA)",
    campaign_category: "Catégorie", campaign_image: "Image de couverture",
    campaign_submit: "Publier la cagnotte",
    donate_title: "Faire un don", donate_amount: "Montant du don",
    donate_name: "Votre nom (optionnel)", donate_anonymous: "Don anonyme",
    donate_country: "Pays", donate_operator: "Opérateur",
    donate_phone: "Numéro de téléphone", donate_confirm: "Confirmer le don",
    payment_pending: "En attente de paiement", payment_timer: "Validez sur votre téléphone dans",
    payment_success: "Paiement confirmé ! Merci pour votre don.",
    payment_failed: "Paiement échoué. Veuillez réessayer.",
    thank_you: "Merci pour votre générosité !",
    error_required: "Ce champ est obligatoire.", error_email: "Email invalide.",
    error_phone: "Numéro invalide.", error_password_match: "Les mots de passe ne correspondent pas.",
    error_password_length: "Le mot de passe doit contenir au moins 8 caractères.",
    success_registered: "Compte créé avec succès !",
    success_logged_in: "Connexion réussie !",
    toggle_theme: "Changer le thème",
  },
  en: {
    nav_home: "Home", nav_campaigns: "Campaigns", nav_how: "How it works",
    nav_testimonials: "Testimonials", nav_login: "Login", nav_register: "Sign up",
    nav_dashboard: "My space", nav_logout: "Logout",
    hero_title: "Let's Collect Together", hero_subtitle: "Create your campaign, share your cause and receive donations via Mobile Money securely.",
    hero_cta1: "Create my campaign", hero_cta2: "View campaigns",
    stats_campaigns: "Campaigns created", stats_donors: "Donors",
    stats_satisfaction: "satisfaction", stats_collected: "FCFA collected",
    how_title: "How does it work?", how_step1_title: "Create", how_step1_desc: "Create your campaign in minutes with a description and a goal.",
    how_step2_title: "Share", how_step2_desc: "Share your unique link on WhatsApp, Facebook, Telegram.",
    how_step3_title: "Collect", how_step3_desc: "Receive donations via Mobile Money and easily withdraw your money.",
    featured_title: "Completed campaigns", featured_subtitle: "Projects successfully funded thanks to your generosity",
    testimonials_title: "What they say about us",
    categories_title: "Browse by category",
    cat_health: "Health", cat_education: "Education", cat_emergency: "Emergency",
    cat_projects: "Projects", cat_humanitarian: "Humanitarian", cat_religion: "Religion",
    cat_sports: "Sports", cat_arts: "Arts & Culture",
    donate_btn: "Donate", share_btn: "Share",
    collected: "collected", goal: "goal", donors: "donors",
    footer_desc: "Secure crowdfunding platform for francophone Africa.",
    footer_links: "Useful links", footer_legal: "Legal notice", footer_privacy: "Privacy",
    footer_contact: "Contact", footer_rights: "All rights reserved.",
    login_title: "Login", login_email: "Email or phone", login_password: "Password",
    login_btn: "Login", login_no_account: "No account yet?",
    login_forgot: "Forgot password?",
    register_title: "Create account", register_name: "Full name", register_email: "Email address",
    register_phone: "Phone number", register_password: "Password",
    register_confirm: "Confirm password", register_btn: "Sign up",
    register_have_account: "Already have an account?",
    dashboard_welcome: "Welcome", dashboard_balance: "Available balance",
    dashboard_pending: "Pending", dashboard_collected: "Total collected",
    dashboard_donations_received: "Donations received",
    kyc_title: "Identity verification", kyc_desc: "Required before any withdrawal.",
    kyc_front: "ID card front", kyc_back: "ID card back",
    kyc_selfie: "Selfie with card", kyc_submit: "Submit",
    kyc_pending: "Pending validation", kyc_approved: "Identity verified",
    kyc_rejected: "Rejected",
    withdraw_title: "Withdrawal request", withdraw_amount: "Desired amount",
    withdraw_commission: "Commission (10%)", withdraw_net: "You receive",
    withdraw_number: "Mobile Money number", withdraw_operator: "Operator",
    withdraw_submit: "Request withdrawal",
    create_campaign: "Create a campaign", campaign_title: "Title",
    campaign_desc: "Description", campaign_goal: "Goal (FCFA)",
    campaign_category: "Category", campaign_image: "Cover image",
    campaign_submit: "Publish campaign",
    donate_title: "Make a donation", donate_amount: "Donation amount",
    donate_name: "Your name (optional)", donate_anonymous: "Anonymous donation",
    donate_country: "Country", donate_operator: "Operator",
    donate_phone: "Phone number", donate_confirm: "Confirm donation",
    payment_pending: "Waiting for payment", payment_timer: "Validate on your phone within",
    payment_success: "Payment confirmed! Thank you for your donation.",
    payment_failed: "Payment failed. Please try again.",
    thank_you: "Thank you for your generosity!",
    error_required: "This field is required.", error_email: "Invalid email.",
    error_phone: "Invalid number.", error_password_match: "Passwords don't match.",
    error_password_length: "Password must be at least 8 characters.",
    success_registered: "Account created successfully!",
    success_logged_in: "Login successful!",
    toggle_theme: "Toggle theme",
  }
};

let currentLang = localStorage.getItem('kotiza_lang') || 'fr';

function t(key) {
  return (translations[currentLang] && translations[currentLang][key]) || translations['fr'][key] || key;
}

function applyTranslations() {
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    el.textContent = t(key);
  });
  document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
    el.placeholder = t(el.getAttribute('data-i18n-placeholder'));
  });
  document.querySelectorAll('[data-i18n-title]').forEach(el => {
    el.title = t(el.getAttribute('data-i18n-title'));
  });
}

function setLang(lang) {
  currentLang = lang;
  localStorage.setItem('kotiza_lang', lang);
  document.documentElement.setAttribute('lang', lang);
  document.querySelectorAll('.lang-btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.lang === lang);
  });
  applyTranslations();
}

document.addEventListener('DOMContentLoaded', () => {
  setLang(currentLang);
  document.querySelectorAll('.lang-btn').forEach(btn => {
    btn.addEventListener('click', () => setLang(btn.dataset.lang));
  });
});
