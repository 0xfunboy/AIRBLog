import { ethers } from 'https://cdn.jsdelivr.net/npm/ethers@5.7.2/dist/ethers.esm.min.js';

const connectButton = document.getElementById('wallet-connect-button');
const errorBox = document.getElementById('wallet-error');

const setError = (message) => {
  if (!errorBox) return;
  if (!message) {
    errorBox.style.display = 'none';
    errorBox.textContent = '';
    return;
  }
  errorBox.textContent = message;
  errorBox.style.display = 'block';
};

const requestNonce = async () => {
  const response = await fetch('/admin/auth/nonce', { credentials: 'same-origin' });
  if (!response.ok) {
    throw new Error('Unable to create login nonce.');
  }
  return response.json();
};

const submitSignature = async (nonce, address, signature) => {
  const response = await fetch('/admin/auth/verify', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body: JSON.stringify({ nonce, address, signature }),
  });

  const data = await response.json();
  if (!response.ok || !data.success) {
    throw new Error(data.error || 'Authentication failed.');
  }
  return data;
};

const disconnectProvider = async (provider) => {
  if (!provider) return;
  const disconnect = provider.disconnect || provider.close;
  if (typeof disconnect === 'function') {
    try {
      await disconnect.call(provider);
    } catch (err) {
      console.warn('Provider disconnect failed', err);
    }
  }
};

const getInjectedProvider = () => {
  const candidates = [];

  if (window.rabby?.provider) {
    candidates.push({ provider: window.rabby.provider, label: 'Rabby' });
  }

  if (window.ethereum) {
    const eth = window.ethereum;
    if (Array.isArray(eth.providers)) {
      eth.providers.forEach((prov) => {
        if (prov?.isRabby) {
          candidates.push({ provider: prov, label: 'Rabby' });
        } else if (prov?.isMetaMask) {
          candidates.push({ provider: prov, label: 'MetaMask' });
        } else {
          candidates.push({ provider: prov, label: 'Injected' });
        }
      });
    } else {
      if (eth.isRabby) {
        candidates.push({ provider: eth, label: 'Rabby' });
      } else if (eth.isMetaMask) {
        candidates.push({ provider: eth, label: 'MetaMask' });
      } else {
        candidates.push({ provider: eth, label: 'Injected' });
      }
    }
  }

  if (window.phantom?.ethereum) {
    candidates.push({ provider: window.phantom.ethereum, label: 'Phantom' });
  }

  if (!candidates.length) {
    return null;
  }

  const preferredOrder = ['Rabby', 'Phantom', 'MetaMask', 'Injected'];
  candidates.sort((a, b) => preferredOrder.indexOf(a.label) - preferredOrder.indexOf(b.label));

  return candidates[0].provider;
};

if (connectButton) {
  const projectId = connectButton.dataset.projectId || '';
  const rpcUrl = connectButton.dataset.rpcUrl || 'https://rpc.ankr.com/eth';

  connectButton.addEventListener('click', async () => {
    setError('');
    connectButton.disabled = true;

    let externalProvider = null;

    try {
      const { nonce, message } = await requestNonce();

      externalProvider = getInjectedProvider();

      if (!externalProvider) {
        if (!projectId) {
          throw new Error('Install MetaMask / Rabby / Phantom or configure WalletConnect project id.');
        }
        const { default: EthereumProvider } = await import('https://esm.sh/@walletconnect/ethereum-provider@2.11.0');
        externalProvider = await EthereumProvider.init({
          projectId,
          showQrModal: true,
          chains: [1],
          optionalChains: [137, 42161, 10, 56, 8453],
          rpcMap: { 1: rpcUrl },
          methods: ['eth_sendTransaction', 'eth_signTransaction', 'eth_sign', 'personal_sign', 'eth_signTypedData'],
            metadata: {
            name: 'AG Blog Admin',
            description: 'Secure admin access',
            url: window.location.origin,
            icons: ['https://blog.airewardrop.xyz/favicon.svg'],
          },
        });
        await externalProvider.connect();
      } else {
        await externalProvider.request?.({ method: 'eth_requestAccounts' });
      }

      const provider = new ethers.providers.Web3Provider(externalProvider);
      const signer = provider.getSigner();
      const address = (await signer.getAddress()).toLowerCase();
      const signature = await signer.signMessage(message);

      const result = await submitSignature(nonce, address, signature);
      if (result.redirect) {
        window.location.href = result.redirect;
      } else {
        setError('Login succeeded, but no redirect was provided.');
      }
    } catch (error) {
      setError(error?.message || 'Unable to authenticate wallet.');
    } finally {
      await disconnectProvider(externalProvider);
      connectButton.disabled = false;
    }
  });
}
