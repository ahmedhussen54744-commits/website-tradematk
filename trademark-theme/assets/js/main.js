/**
 * Trademark 3D Theme - Main JavaScript
 * Advanced 3D animations, particles, and interactions
 */

(function() {
    'use strict';

    // ===== LOADER =====
    window.addEventListener('load', function() {
        const loader = document.getElementById('tmLoader');
        if (loader) {
            setTimeout(function() {
                loader.classList.add('hidden');
            }, 800);
        }
    });

    // ===== 3D PARTICLE BACKGROUND =====
    function initParticles() {
        const container = document.getElementById('tmParticles');
        if (!container) return;

        for (let i = 0; i < 30; i++) {
            const particle = document.createElement('div');
            particle.className = 'tm-particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 8 + 's';
            particle.style.animationDuration = (6 + Math.random() * 6) + 's';
            particle.style.width = (2 + Math.random() * 4) + 'px';
            particle.style.height = particle.style.width;
            container.appendChild(particle);
        }
    }

    // ===== THREE.JS 3D BACKGROUND =====
    function init3DBackground() {
        const canvas = document.getElementById('tmBgCanvas');
        if (!canvas || typeof THREE === 'undefined') return;

        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        canvas.appendChild(renderer.domElement);

        // Create floating geometric shapes
        const geometries = [];
        const materials = [
            new THREE.MeshBasicMaterial({ color: 0xffd700, wireframe: true, transparent: true, opacity: 0.15 }),
            new THREE.MeshBasicMaterial({ color: 0x3949ab, wireframe: true, transparent: true, opacity: 0.1 }),
            new THREE.MeshBasicMaterial({ color: 0x00c853, wireframe: true, transparent: true, opacity: 0.08 }),
        ];

        // Add icosahedrons
        for (let i = 0; i < 5; i++) {
            const geo = new THREE.IcosahedronGeometry(1 + Math.random() * 2, 1);
            const mesh = new THREE.Mesh(geo, materials[i % materials.length]);
            mesh.position.set(
                (Math.random() - 0.5) * 20,
                (Math.random() - 0.5) * 20,
                (Math.random() - 0.5) * 10 - 5
            );
            mesh.userData = {
                rotSpeed: { x: Math.random() * 0.005, y: Math.random() * 0.005 },
                floatSpeed: Math.random() * 0.002,
                floatOffset: Math.random() * Math.PI * 2
            };
            scene.add(mesh);
            geometries.push(mesh);
        }

        // Add torus knots
        for (let i = 0; i < 3; i++) {
            const geo = new THREE.TorusKnotGeometry(0.8, 0.2, 64, 8);
            const mesh = new THREE.Mesh(geo, materials[1]);
            mesh.position.set(
                (Math.random() - 0.5) * 15,
                (Math.random() - 0.5) * 15,
                -8 - Math.random() * 5
            );
            mesh.userData = {
                rotSpeed: { x: Math.random() * 0.003, y: Math.random() * 0.004 },
                floatSpeed: Math.random() * 0.001,
                floatOffset: Math.random() * Math.PI * 2
            };
            scene.add(mesh);
            geometries.push(mesh);
        }

        camera.position.z = 8;

        // Mouse interaction
        let mouseX = 0, mouseY = 0;
        document.addEventListener('mousemove', function(e) {
            mouseX = (e.clientX / window.innerWidth - 0.5) * 2;
            mouseY = (e.clientY / window.innerHeight - 0.5) * 2;
        });

        // Animation loop
        function animate() {
            requestAnimationFrame(animate);

            geometries.forEach(function(mesh) {
                mesh.rotation.x += mesh.userData.rotSpeed.x;
                mesh.rotation.y += mesh.userData.rotSpeed.y;
                mesh.position.y += Math.sin(Date.now() * mesh.userData.floatSpeed + mesh.userData.floatOffset) * 0.005;
            });

            camera.position.x += (mouseX * 0.5 - camera.position.x) * 0.02;
            camera.position.y += (-mouseY * 0.5 - camera.position.y) * 0.02;
            camera.lookAt(scene.position);

            renderer.render(scene, camera);
        }
        animate();

        // Resize handler
        window.addEventListener('resize', function() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
    }

    // ===== SCROLL ANIMATIONS WITH GSAP =====
    function initScrollAnimations() {
        if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

        gsap.registerPlugin(ScrollTrigger);

        // Animate cards on scroll
        gsap.utils.toArray('.tm-card').forEach(function(card, i) {
            gsap.from(card, {
                scrollTrigger: {
                    trigger: card,
                    start: 'top 85%',
                    toggleActions: 'play none none none'
                },
                y: 60,
                opacity: 0,
                duration: 0.8,
                delay: i * 0.1,
                ease: 'power3.out'
            });
        });

        // Header shrink on scroll
        ScrollTrigger.create({
            start: 'top -80',
            onEnter: function() {
                document.getElementById('tmHeader').style.padding = '10px 30px';
                document.getElementById('tmHeader').style.background = 'rgba(10, 14, 39, 0.95)';
            },
            onLeaveBack: function() {
                document.getElementById('tmHeader').style.padding = '15px 30px';
                document.getElementById('tmHeader').style.background = 'rgba(10, 14, 39, 0.9)';
            }
        });
    }

    // ===== MOBILE MENU =====
    function initMobileMenu() {
        const btn = document.getElementById('tmMobileMenu');
        const nav = document.getElementById('tmNav');
        if (!btn || !nav) return;

        btn.addEventListener('click', function() {
            if (nav.style.display === 'flex') {
                nav.style.display = 'none';
            } else {
                nav.style.display = 'flex';
                nav.style.flexDirection = 'column';
                nav.style.position = 'absolute';
                nav.style.top = '100%';
                nav.style.left = '0';
                nav.style.right = '0';
                nav.style.background = 'rgba(10, 14, 39, 0.98)';
                nav.style.padding = '20px';
                nav.style.borderBottom = '1px solid rgba(255,255,255,0.1)';
            }
        });
    }

    // ===== FILE UPLOAD PREVIEW =====
    function initFileUpload() {
        const fileInputs = document.querySelectorAll('.tm-file-upload input[type="file"]');
        fileInputs.forEach(function(input) {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const parent = input.closest('.tm-file-upload');
                    const text = parent.querySelector('div');
                    if (text) {
                        text.innerHTML = '<div style="font-size: 36px; margin-bottom: 10px;">&#10004;</div>' +
                            '<p style="color: var(--accent); font-size: 14px;">' + file.name + '</p>' +
                            '<p style="color: rgba(255,255,255,0.3); font-size: 12px;">' + 
                            (file.size / 1024 / 1024).toFixed(2) + ' MB</p>';
                    }
                }
            });
        });
    }

    // ===== FORM VALIDATION =====
    function initFormValidation() {
        const form = document.getElementById('tmApplyForm');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            const fileInput = form.querySelector('input[name="tm_brand_logo"]');
            if (fileInput && fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                if (file.size > maxSize) {
                    e.preventDefault();
                    alert('File size must be less than 5MB');
                    return false;
                }
                
                const allowedTypes = ['image/png', 'image/jpeg', 'image/svg+xml'];
                if (!allowedTypes.includes(file.type)) {
                    e.preventDefault();
                    alert('Only PNG, JPG or SVG files are allowed');
                    return false;
                }
            }
        });
    }

    // ===== INIT ALL =====
    document.addEventListener('DOMContentLoaded', function() {
        initParticles();
        init3DBackground();
        initScrollAnimations();
        initMobileMenu();
        initFileUpload();
        initFormValidation();
    });

})();
