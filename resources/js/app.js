import './bootstrap';

import Sidebar from './modules/sidebar';
import Dashboard from './modules/dashboard';

import Signature from './modules/signature';
import SignatureDraw from './modules/signature-draw';

import Approver from './modules/approver';

import DocumentSearch from './modules/document-search';
import UserSearch from './modules/user-search';
import ChatSearch from './modules/chat-search';

import PdfViewer from './modules/pdf-viewer';

import UploadModal from './modules/upload-modal';
import RejectModal from './modules/reject-modal';

import UserProfile from './modules/user-profile';

import MoreMenu from './modules/more-menu';
import { initRoleAssignmentForm } from './modules/role-assignment';

import Auth from './modules/auth';
import PasswordModal from './modules/password-modal';
import * as bootstrap from 'bootstrap';
import Chat from './modules/chat';
import Messenger from './modules/messenger';

import './modules/purchase-order-queue';

window.bootstrap = bootstrap;
window.Chat = Chat;
window.Messenger = Messenger;

document.addEventListener('DOMContentLoaded', () => {
    Auth.init();

    Sidebar.init();

    Messenger.init();
    
    Dashboard.init();

    Signature.init();

    SignatureDraw.init();

    Approver.init();

    DocumentSearch.init();

    UserSearch.init();

    ChatSearch.init();
    
    PdfViewer.init();

    UploadModal.init();

    RejectModal.init();

    UserProfile.init();

    MoreMenu.init();

    PasswordModal.init();
    
    Chat.init();

    initRoleAssignmentForm();
});